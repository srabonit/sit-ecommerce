<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\SmsProvider;
use App\Models\SmsSetting;
use App\Services\Sms\Drivers\OnecodesoftDriver;
use App\Services\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class SmsSettingController extends Controller
{
    /**
     * Display the SMS settings, provider credentials, and trigger configurations.
     */
    public function index(): View
    {
        $provider = SmsProvider::where('driver', 'onecodesoft')
            ->orWhere('name', 'Onecodesoft')
            ->first();

        // Fallback default provider if not found
        if (!$provider) {
            $provider = SmsProvider::create([
                'name'          => 'Onecodesoft',
                'driver'        => 'onecodesoft',
                'base_url'      => 'https://sms.ocs-api.top/api',
                'sender_id'     => '',
                'business_name' => config('app.name', 'SHOP NOW BD'),
                'price_per_sms' => 0.3500,
                'balance'       => 0.00,
                'is_active'     => true,
                'sandbox_mode'  => false,
            ]);
        } elseif (empty($provider->base_url) || str_contains($provider->base_url, 'api.onecodesoft.com')) {
            $provider->update(['base_url' => 'https://sms.ocs-api.top/api']);
        }

        // Fetch settings and guarantee default string values for Blade checkboxes
        $rawSettings = SmsSetting::pluck('value', 'key')->toArray();

        $defaultSettings = [
            'global_sms_enabled'                => '1',
            'manual_order_sms_enabled'          => '1',
            'global_email_enabled'              => '1',
            'auto_email_order_status'           => '1',
            'bulk_sms_enabled'                  => '1',
            'sms_marketing_enabled'             => '1',
            'admin_notification_phone'          => '',
            'auto_sms_admin_new_order'          => '1',
            'auto_sms_admin_low_balance'        => '1',
            'low_balance_threshold'             => '50.00',

            // Complete 10 Lifecycle Status Trigger Switches
            'auto_sms_order_placed_customer'    => '1', // 1. Pending (Order Placed)
            'auto_sms_order_received'           => '1', // 2. Received
            'auto_sms_order_confirmed'          => '1', // 3. Confirmed
            'auto_sms_order_processing'         => '1', // 4. Processing (Packed)
            'auto_sms_order_shipped'            => '1', // 5. Shipped
            'auto_sms_order_attempt_to_deliver' => '1', // 6. Attempt to Deliver
            'auto_sms_order_delivered'          => '1', // 7. Delivered
            'auto_sms_order_exchange'           => '1', // 8. Exchange
            'auto_sms_order_returned'           => '1', // 9. Returned
            'auto_sms_order_cancelled'          => '1', // 10. Cancelled

            'auto_sms_customer_welcome'         => '1',
            'otp_mode'                          => 'identity',
            'otp_expiry_minutes'                => '5',
            'otp_resend_cooldown_seconds'       => '60',
            'max_otp_attempts'                  => '3',
            'max_daily_otp_per_number'          => '5',
            'max_daily_otp_per_ip'              => '15',
        ];

        $settings = array_merge($defaultSettings, $rawSettings);

        return view('admin.sms.settings', compact('provider', 'settings'));
    }

    /**
     * Update global SMS triggers, admin alerts, and OTP security policies.
     */
    public function update(Request $request): RedirectResponse
    {
        $currentTab = $request->input('current_tab', 'tab-general');

        // 1. General & Automated Triggers Section
        if ($request->input('setting_section') === 'general' || $request->has('low_balance_threshold')) {
            $booleanKeys = [
                'global_sms_enabled',
                'manual_order_sms_enabled',
                'global_email_enabled',
                'auto_email_order_status',
                'bulk_sms_enabled',
                'sms_marketing_enabled',
                'auto_sms_admin_new_order',
                'auto_sms_admin_low_balance',

                // 10 Lifecycle Status Switches
                'auto_sms_order_placed_customer',
                'auto_sms_order_received',
                'auto_sms_order_confirmed',
                'auto_sms_order_processing',
                'auto_sms_order_shipped',
                'auto_sms_order_attempt_to_deliver',
                'auto_sms_order_delivered',
                'auto_sms_order_exchange',
                'auto_sms_order_returned',
                'auto_sms_order_cancelled',

                'auto_sms_customer_welcome',
            ];

            foreach ($booleanKeys as $key) {
                $val = $request->has($key) ? '1' : '0';
                SmsSetting::setVal($key, $val, 'general', 'boolean');
            }

            // Normalize admin notification phone
            $rawPhone = trim((string) $request->input('admin_notification_phone', ''));
            if (!empty($rawPhone)) {
                $rawPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($rawPhone, '01')) {
                    $rawPhone = '88' . $rawPhone;
                }
            }
            SmsSetting::setVal('admin_notification_phone', $rawPhone, 'general', 'string');

            // Low balance threshold
            $threshold = (float) $request->input('low_balance_threshold', 50.00);
            SmsSetting::setVal('low_balance_threshold', number_format($threshold, 2, '.', ''), 'general', 'decimal');

            return redirect()->route('admin.sms.settings.index', ['tab' => $currentTab])
                ->with('success', 'SMS settings and status notification triggers saved successfully.');
        }

        // 2. OTP Security Policies Section
        if ($request->input('setting_section') === 'otp' || $request->has('otp_mode')) {
            $otpKeys = [
                'otp_mode',
                'otp_expiry_minutes',
                'otp_resend_cooldown_seconds',
                'max_otp_attempts',
                'max_daily_otp_per_number',
                'max_daily_otp_per_ip',
            ];

            foreach ($otpKeys as $key) {
                if ($request->has($key)) {
                    SmsSetting::setVal($key, (string) $request->input($key), 'otp', 'string');
                }
            }

            return redirect()->route('admin.sms.settings.index', ['tab' => $currentTab])
                ->with('success', 'OTP security policies saved successfully.');
        }

        return redirect()->route('admin.sms.settings.index', ['tab' => $currentTab])
            ->with('info', 'No configuration changes detected.');
    }

    /**
     * Update Onecodesoft Gateway API credentials, sender ID, and pricing.
     */
    public function updateProvider(Request $request): RedirectResponse
    {
        $provider = null;
        if ($request->filled('provider_id')) {
            $provider = SmsProvider::find($request->input('provider_id'));
        }
        if (!$provider) {
            $provider = SmsProvider::where('driver', 'onecodesoft')->first() ?: new SmsProvider();
        }

        $provider->name     = 'Onecodesoft';
        $provider->driver   = 'onecodesoft';
        $provider->base_url = 'https://sms.ocs-api.top/api';

        // Save API Key / Token securely
        $inputToken = trim((string) ($request->input('api_token') ?: $request->input('api_key')));
        if (!empty($inputToken)) {
            $provider->api_token = $inputToken;
            if (Schema::hasColumn('sms_providers', 'api_key')) {
                $provider->api_key = $inputToken;
            }
            if (Schema::hasColumn('sms_providers', 'token')) {
                $provider->token = $inputToken;
            }
        }

        $cleanSenderId = trim((string) $request->input('sender_id', ''));
        $provider->sender_id     = (strtolower($cleanSenderId) === 'default') ? '' : $cleanSenderId;
        $provider->business_name = trim((string) $request->input('business_name', $provider->business_name));
        $provider->price_per_sms = (float) $request->input('price_per_sms', 0.3500);
        $provider->is_active     = $request->has('is_active');
        $provider->sandbox_mode  = $request->has('sandbox_mode');

        $provider->save();

        // Attempt instant balance sync with the updated driver instance
        try {
            $driver = new OnecodesoftDriver($provider);
            $driver->checkBalance();
        } catch (Throwable $e) {
            Log::warning('Automatic balance sync on credentials update failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.sms.settings.index', ['tab' => 'tab-provider'])
            ->with('success', "Provider '{$provider->name}' credentials and settings updated successfully.");
    }

    /**
     * Sync real-time account balance from Onecodesoft API (AJAX).
     */
    public function syncBalance(): JsonResponse
    {
        $provider = SmsProvider::where('driver', 'onecodesoft')
            ->orWhere('name', 'Onecodesoft')
            ->first();

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'SMS Gateway provider is not configured.',
            ], 404);
        }

        if (empty($provider->base_url) || str_contains($provider->base_url, 'api.onecodesoft.com')) {
            $provider->update(['base_url' => 'https://sms.ocs-api.top/api']);
        }

        $driver = new OnecodesoftDriver($provider);
        $result = $driver->checkBalance();

        if (empty($result['status'])) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to connect to Onecodesoft API. Check your API token.',
            ], 422);
        }

        $provider->refresh();

        return response()->json([
            'success'       => true,
            'balance'       => number_format((float) ($result['balance'] ?? $provider->balance ?? 0), 2),
            'estimated_sms' => number_format((int) ($result['estimated_sms'] ?? $provider->estimated_sms ?? 0)),
            'message'       => 'Live Gateway balance synced successfully.',
        ]);
    }

    /**
     * Send instant test SMS to verify gateway connection (AJAX).
     */
    public function testSms(Request $request, SmsService $smsService): JsonResponse
    {
        $rawPhone   = (string) ($request->json('phone_number') ?: $request->input('phone_number'));
        $rawMessage = (string) ($request->json('message') ?: $request->input('message'));

        $rawPhone = trim($rawPhone);
        if (empty($rawPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a recipient mobile number.',
            ], 422);
        }

        $provider = SmsProvider::where('driver', 'onecodesoft')
            ->orWhere('name', 'Onecodesoft')
            ->first();

        if (empty($provider->base_url) || str_contains($provider->base_url, 'api.onecodesoft.com')) {
            $provider->update(['base_url' => 'https://sms.ocs-api.top/api']);
        }

        $driver = new OnecodesoftDriver($provider);
        $cleanPhone = $driver->formatNumber($rawPhone);

        if (!preg_match('/^8801[3-9]\d{8}$/', $cleanPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Bangladeshi mobile number format (e.g. 017XXXXXXXX or 88017XXXXXXXX).',
            ], 422);
        }

        $senderId = ($provider && !empty($provider->sender_id) && strtolower($provider->sender_id) !== 'default')
            ? $provider->sender_id
            : null;

        $messageText = !empty(trim($rawMessage))
            ? trim($rawMessage)
            : 'Test SMS from ' . config('app.name', 'Shop Now BD') . ' gateway at ' . now()->format('h:i:s A');

        $metrics = $smsService->analyzeMessage($messageText);

        try {
            // Dispatch directly through OnecodesoftDriver
            $response = $driver->sendSingle($cleanPhone, $messageText, $senderId);
            $isSuccess = !empty($response['status']);
            $cost = ($provider ? (float) $provider->price_per_sms : 0.35) * $metrics['sms_parts'];

            // If gateway returns updated balance directly in send response, sync it
            if (isset($response['data']['data']['balance'])) {
                $newBal = (float) $response['data']['data']['balance'];
                $unitPrice = $provider->price_per_sms > 0 ? (float) $provider->price_per_sms : 0.35;
                $provider->update([
                    'balance'                => $newBal,
                    'estimated_sms'          => (int) floor($newBal / $unitPrice),
                    'last_balance_synced_at' => now(),
                ]);
            }

            // Log the attempt safely in audit logs
            try {
                SmsLog::create([
                    'recipient'        => $cleanPhone,
                    'sender_id'        => $senderId ?: 'Default',
                    'provider'         => 'onecodesoft',
                    'sms_type'         => 'transactional',
                    'message_content'  => $messageText,
                    'characters_count' => $metrics['characters_count'],
                    'sms_parts'        => $metrics['sms_parts'],
                    'cost'             => $cost,
                    'status'           => $isSuccess ? 'sent' : 'failed',
                    'message_id'       => $response['message_id'] ?? null,
                    'status_code'      => $isSuccess ? 200 : 422,
                    'gateway_response' => json_encode($response['data'] ?? $response),
                    'is_sandbox'       => (bool) ($provider->sandbox_mode ?? false),
                    'sent_at'          => $isSuccess ? now() : null,
                ]);
            } catch (Throwable $logEx) {
                Log::warning('SmsLog write skipped on test SMS: ' . $logEx->getMessage());
            }

            if ($isSuccess) {
                return response()->json([
                    'success' => true,
                    'message' => $response['message'] ?? "Test SMS sent successfully to {$cleanPhone}.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gateway Error: ' . ($response['message'] ?? 'Failed to deliver SMS. Check API Key or Sender ID.'),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Test SMS dispatch exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ], 500);
        }
    }
}