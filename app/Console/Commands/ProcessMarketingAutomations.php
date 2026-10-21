<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\EmailAutomation;
use App\Models\EmailAutomationStep;
use App\Models\EmailAutomationLog;
use App\Models\EmailAbandonedCart;
use App\Models\EmailTemplate;
use App\Models\EmailSuppressionList;

class ProcessMarketingAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-marketing:process-automations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches automated marketing workflows such as abandoned cart recovery sequences with custom timing delays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=====================================================');
        $this->info('--> [' . now()->format('Y-m-d h:i:s A') . '] Starting Automation Processing');
        $this->info('=====================================================');

        // ১. সক্রিয় সব অটোমেশন নিয়ে আসা
        $activeAutomations = EmailAutomation::with(['steps.template'])
            ->where('is_active', true)
            ->get();

        if ($activeAutomations->isEmpty()) {
            $this->warn('No active marketing automation workflows found in database.');
            return Command::SUCCESS;
        }

        $this->info("Found {$activeAutomations->count()} active workflow(s) to evaluate.");

        foreach ($activeAutomations as $automation) {
            // স্পেস, আন্ডারস্কোর বা হাইফেন যাই থাকুক তা পরিষ্কার করা (Abandoned cart বা abandoned_cart উভয়ই ধরবে)
            $rawEvent = strtolower(trim($automation->trigger_event));
            $cleanEvent = preg_replace('/[^a-z0-9]/', '', $rawEvent);

            $this->line("Evaluating Workflow #{$automation->id}: '{$automation->name}' | Trigger: '{$automation->trigger_event}'");

            if (str_contains($cleanEvent, 'abandoned')) {
                $this->processAbandonedCartWorkflow($automation);
            } else {
                $this->line("--> Skipping Workflow #{$automation->id}: Non-cart trigger event.");
            }
        }

        $this->info('=====================================================');
        $this->info('--> Automation execution cycle completed successfully.');
        $this->info('=====================================================');
        return Command::SUCCESS;
    }

    /**
     * Process Abandoned Cart Automation with step-level custom delays (5 min, 10 min, etc.)
     */
    protected function processAbandonedCartWorkflow(EmailAutomation $automation)
    {
        $steps = $automation->steps()->orderBy('step_order', 'asc')->get();

        if ($steps->isEmpty()) {
            $this->warn("--> Workflow '{$automation->name}' (#{$automation->id}) has no steps configured. Skipping.");
            return;
        }

        $this->info("--> Loaded {$steps->count()} sequence step(s) for workflow: {$automation->name}");

        // ডাটাবেজের সব পেন্ডিং ও আনরিকভার্ড কার্ট নিয়ে আসা
        $unrecoveredCarts = EmailAbandonedCart::whereNotNull('email')
            ->where(function ($q) {
                if (Schema::hasColumn('email_abandoned_carts', 'is_recovered')) {
                    $q->where('is_recovered', false)->orWhere('is_recovered', 0);
                }
                if (Schema::hasColumn('email_abandoned_carts', 'recovery_status')) {
                    $q->orWhere('recovery_status', 'pending');
                }
            })
            ->get();

        $this->line("--> Found {$unrecoveredCarts->count()} pending unrecovered cart(s) in database.");

        if ($unrecoveredCarts->isEmpty()) {
            return;
        }

        $storeName = config('app.name', 'Shop Now BD');

        foreach ($steps as $stepIndex => $step) {
            $delayMinutes = (int) ($step->delay_minutes ?? 0);
            $this->info("-------------------------------------------------------------");
            $this->info("Checking Step #{$step->step_order} [Configured Delay: {$delayMinutes} minute(s)]");
            $this->info("-------------------------------------------------------------");

            foreach ($unrecoveredCarts as $cart) {
                $email = strtolower(trim($cart->email));

                // ১. ভ্যালিড ইমেইল ফরম্যাট যাচাই
                if (empty($email) || str_contains($email, '@guest.') || str_contains($email, '@temporary.') || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->line("--> Cart #{$cart->id}: Invalid or guest dummy email address. Skipping.");
                    continue;
                }

                // ২. কার্ট তৈরির পর কত মিনিট অতিক্রান্ত হয়েছে তা নিখুঁত হিসাব
                $cartTime = !empty($cart->abandoned_at)
                    ? Carbon::parse($cart->abandoned_at)
                    : Carbon::parse($cart->created_at);

                $minutesPassed = abs(now()->diffInMinutes($cartTime));

                if ($minutesPassed < $delayMinutes) {
                    $remaining = $delayMinutes - $minutesPassed;
                    $this->line("--> Cart #{$cart->id} ({$email}): {$minutesPassed}m passed. Needs {$remaining}m more to satisfy {$delayMinutes}m delay.");
                    continue;
                }

                // ৩. কার্ট ছাড়ার সময়ের পর কাস্টমার কোনো অর্ডার প্লেস করেছে কি না (Old Order Conflict ফিক্স)
                $orderEmailCol = collect(['customer_email', 'email', 'billing_email'])
                    ->first(fn($c) => Schema::hasTable('orders') && Schema::hasColumn('orders', $c)) ?? 'customer_email';

                $orderStatusCol = collect(['order_status', 'status'])
                    ->first(fn($c) => Schema::hasTable('orders') && Schema::hasColumn('orders', $c)) ?? 'order_status';

                $hasBoughtAfter = DB::table('orders')->where(function ($q) use ($cart, $email, $orderEmailCol) {
                    $q->where($orderEmailCol, $email);
                    if (!empty($cart->phone)) {
                        $clean = preg_replace('/[^0-9]/', '', $cart->phone);
                        $phoneCols = ['customer_phone', 'phone', 'billing_phone'];
                        foreach ($phoneCols as $pCol) {
                            if (Schema::hasColumn('orders', $pCol)) {
                                $q->orWhere($pCol, $cart->phone)->orWhere($pCol, $clean);
                            }
                        }
                    }
                })
                ->where('created_at', '>=', $cartTime->copy()->subMinutes(2))
                ->whereNotIn($orderStatusCol, ['cancelled', 'failed', 'declined'])
                ->exists();

                if ($hasBoughtAfter) {
                    $cart->update([
                        'is_recovered' => true,
                        'recovered_at' => now(),
                    ]);
                    $this->line("--> Cart #{$cart->id} ({$email}): Placed an order after abandonment. Marked as recovered.");
                    continue;
                }

                // ৪. সাপ্রেশন ব্ল্যাকলিস্ট ফিল্টার
                if (EmailSuppressionList::isSuppressed($email)) {
                    $this->warn("--> Cart #{$cart->id} ({$email}): Suppressed email address. Skipping.");
                    continue;
                }

                // ৫. ডুপ্লিকেট ডিসপ্যাচ রোধ (Idempotency Key ভ্যালিডেশন)
                $idempotencyKey = hash('sha256', "{$automation->id}_{$step->id}_{$cart->id}_{$email}");
                $alreadyLogged = false;

                if (Schema::hasColumn('email_automation_logs', 'idempotency_key')) {
                    $alreadyLogged = EmailAutomationLog::where('idempotency_key', $idempotencyKey)->exists();
                } else {
                    $alreadyLogged = EmailAutomationLog::where('automation_id', $automation->id)
                        ->where('step_id', $step->id)
                        ->where('abandoned_cart_id', $cart->id)
                        ->exists();
                }

                if ($alreadyLogged) {
                    $this->line("--> Cart #{$cart->id} ({$email}): Already received Step #{$step->step_order}. Skipping.");
                    continue;
                }

                // ৬. ড্রিপ সিকোয়েন্স চেইনিং (পূর্ববর্তী স্টেপ সফল হওয়া আবশ্যক)
                if ($stepIndex > 0) {
                    $prevStep = $steps[$stepIndex - 1];
                    $prevStepSent = EmailAutomationLog::where('automation_id', $automation->id)
                        ->where('step_id', $prevStep->id)
                        ->where('abandoned_cart_id', $cart->id)
                        ->where('status', 'sent')
                        ->exists();

                    if (!$prevStepSent) {
                        $this->line("--> Cart #{$cart->id} ({$email}): Waiting for Step #{$prevStep->step_order} completion.");
                        continue;
                    }
                }

                // ৭. কার্ট আইটেমস টেবিল জেনারেশন
                $items = is_array($cart->cart_items) ? $cart->cart_items : json_decode($cart->cart_items, true);
                $itemsHtml = '';
                if (!empty($items) && is_array($items)) {
                    foreach ($items as $it) {
                        $pName  = htmlspecialchars($it['name'] ?? ($it['title'] ?? 'Product'));
                        $pQty   = (int)($it['quantity'] ?? 1);
                        $pPrice = number_format((float)($it['price'] ?? 0), 2);
                        $itemsHtml .= "<tr>
                            <td style='padding: 10px; border-bottom: 1px solid #f1f5f9; color: #334155;'>{$pName}</td>
                            <td style='padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: center; color: #64748b;'>{$pQty}</td>
                            <td style='padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: bold; color: #0f172a;'>৳ {$pPrice}</td>
                        </tr>";
                    }
                }

                $customerName = htmlspecialchars($cart->customer_name ?: 'Valued Customer');
                $cTotal       = number_format((float)$cart->cart_value, 2);
                $recoveryUrl  = $cart->recovery_url;
                $subject      = $step->subject ?: "Complete your order at {$storeName} - Items waiting in your cart!";

                // ৮. ডিজাইন টেমপ্লেট অথবা ফলব্যাক এইচটিএমএল
                if ($step->template) {
                    $rendered = $step->template->render([
                        'customer_name' => $customerName,
                        'first_name'    => $customerName,
                        'email'         => $email,
                        'store_name'    => $storeName,
                        'cart_value'    => $cTotal,
                        'recovery_url'  => $recoveryUrl,
                        'cart_items'    => $itemsHtml,
                    ]);
                    $htmlContent = str_replace(
                        ['{recovery_url}', '{target_url}', '{cart_value}', '{cart_items}'],
                        [$recoveryUrl, $recoveryUrl, $cTotal, $itemsHtml],
                        $rendered
                    );
                } else {
                    $htmlContent = "
                    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                        <div style='text-align: center; margin-bottom: 25px;'>
                            <h2 style='color: #0f172a; margin-bottom: 6px; font-weight: 800;'>{$storeName}</h2>
                            <p style='color: #64748b; font-size: 14px; margin-top: 0;'>Did you forget something in your shopping cart?</p>
                        </div>
                        <p style='color: #1e293b; font-size: 15px;'>Hello <strong>{$customerName}</strong>,</p>
                        <p style='color: #475569; font-size: 14px; line-height: 1.6;'>
                            We noticed you left items in your shopping cart without completing your order. We have reserved them for you so you can easily complete your purchase!
                        </p>
                        <div style='margin: 22px 0; border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden;'>
                            <table style='width: 100%; border-collapse: collapse; font-size: 13.5px;'>
                                <thead>
                                    <tr style='background-color: #f8fafc; color: #475569;'>
                                        <th style='padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0;'>Item</th>
                                        <th style='padding: 10px; text-align: center; border-bottom: 2px solid #e2e8f0;'>Qty</th>
                                        <th style='padding: 10px; text-align: right; border-bottom: 2px solid #e2e8f0;'>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$itemsHtml}
                                </tbody>
                                <tfoot>
                                    <tr style='background-color: #f8fafc;'>
                                        <td colspan='2' style='padding: 12px 10px; text-align: right; font-weight: bold; color: #1e293b;'>Total:</td>
                                        <td style='padding: 12px 10px; text-align: right; font-weight: 800; color: #2563eb;'>৳ {$cTotal}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$recoveryUrl}' style='background-color: #f59e0b; color: #ffffff; padding: 13px 32px; text-decoration: none; font-size: 15px; font-weight: bold; border-radius: 6px; display: inline-block; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.35);'>
                                Complete Your Purchase Now &rarr;
                            </a>
                        </div>
                        <p style='color: #94a3b8; font-size: 12px; text-align: center; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 15px; line-height: 1.5;'>
                            If you already placed this order, please disregard this email.<br>
                            &copy; " . date('Y') . " {$storeName}. All rights reserved.
                        </p>
                    </div>";
                }

                // ৯. ইমেইল প্রেরণ ও ডাটাবেজ লগ
                try {
                    Mail::html($htmlContent, function ($msg) use ($email, $subject, $storeName) {
                        $msg->to($email)->subject($subject);
                    });

                    $logData = [
                        'automation_id'     => $automation->id,
                        'step_id'           => $step->id,
                        'abandoned_cart_id' => $cart->id,
                        'email'             => $email,
                        'status'            => 'sent',
                        'executed_at'       => now(),
                    ];
                    if (Schema::hasColumn('email_automation_logs', 'idempotency_key')) {
                        $logData['idempotency_key'] = $idempotencyKey;
                    }
                    EmailAutomationLog::create($logData);

                    if (method_exists($cart, 'recordReminderSent')) {
                        $cart->recordReminderSent();
                    } else {
                        $cart->increment('reminders_sent');
                        $cart->update(['last_reminder_at' => now()]);
                    }

                    if (method_exists($automation, 'recordTriggered')) {
                        $automation->recordTriggered();
                    }

                    $this->info(">>> [SUCCESS] Sent Step #{$step->step_order} recovery email to {$email} (Cart #{$cart->id})");
                } catch (\Throwable $e) {
                    $this->error(">>> [FAILED] Could not send email to {$email}: " . $e->getMessage());

                    EmailAutomationLog::create([
                        'automation_id'     => $automation->id,
                        'step_id'           => $step->id,
                        'abandoned_cart_id' => $cart->id,
                        'email'             => $email,
                        'status'            => 'failed',
                        'error_message'     => substr($e->getMessage(), 0, 250),
                        'executed_at'       => now(),
                    ]);
                }

                usleep(25000);
            }
        }
    }
}