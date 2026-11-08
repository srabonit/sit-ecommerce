<?php

namespace App\Services\Sms;

use App\Models\CommunicationPreference;
use App\Models\SmsBlacklist;
use App\Models\SmsLog;
use App\Models\SmsProvider;
use App\Models\SmsSetting;
use App\Models\SmsTemplate;
use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Drivers\OnecodesoftDriver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SmsService
{
    protected ?SmsProvider $provider;
    protected SmsProviderInterface $driver;

    public function __construct(?SmsProvider $provider = null)
    {
        if ($provider) {
            $this->provider = $provider;
        } else {
            $query = SmsProvider::where('is_active', true);
            if (Schema::hasColumn('sms_providers', 'is_default')) {
                $query->orderBy('is_default', 'desc');
            }
            $this->provider = $query->first() 
                ?? SmsProvider::where('driver', 'onecodesoft')->first() 
                ?? SmsProvider::first();
        }

        // Ensure default provider configuration has valid base URL
        if ($this->provider && (empty($this->provider->base_url) || str_contains($this->provider->base_url, 'api.onecodesoft.com'))) {
            $this->provider->base_url = 'https://sms.ocs-api.top/api';
        }

        $this->driver = $this->resolveDriver($this->provider);
    }

    /**
     * Resolve provider driver implementation dynamically
     */
    protected function resolveDriver(?SmsProvider $provider): SmsProviderInterface
    {
        $driverName = strtolower((string) ($provider?->driver ?? 'onecodesoft'));

        return match ($driverName) {
            'onecodesoft' => new OnecodesoftDriver($provider),
            default       => new OnecodesoftDriver($provider),
        };
    }

    /**
     * Set driver explicitly on runtime if needed
     */
    public function setProvider(SmsProvider $provider): self
    {
        $this->provider = $provider;
        $this->driver = $this->resolveDriver($provider);

        return $this;
    }

    /**
     * Get the active driver instance
     */
    public function getDriver(): SmsProviderInterface
    {
        return $this->driver;
    }

    /**
     * Clean and format phone numbers to standard 8801XXXXXXXXX format
     */
    public function formatNumber(string $number): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($cleaned, '01') && strlen($cleaned) === 11) {
            return '88' . $cleaned;
        }

        if (str_starts_with($cleaned, '1') && strlen($cleaned) === 10) {
            return '880' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Calculate character count, Unicode detection, and SMS parts
     * GSM: 160 chars for 1 part, 153 chars per part for multi-part
     * Unicode (Bangla/Emojis): 70 chars for 1 part, 67 chars per part for multi-part
     */
    public function analyzeMessage(string $text): array
    {
        $isUnicode = (bool) preg_match('/[^\x20-\x7E\r\n\t]/u', $text);
        $charCount = mb_strlen($text, 'UTF-8');

        if ($isUnicode) {
            $parts = $charCount <= 70 ? 1 : (int) ceil($charCount / 67);
        } else {
            $parts = $charCount <= 160 ? 1 : (int) ceil($charCount / 153);
        }

        return [
            'is_unicode'       => $isUnicode,
            'characters_count' => $charCount,
            'sms_parts'        => max(1, $parts),
        ];
    }

    /**
     * Core method to dispatch single SMS with instant real-time balance sync and logging
     */
    public function send(string $number, string $message, string $type = 'transactional', array $options = []): array
    {
        $recipient = $this->formatNumber($number);

        // 1. Check Global System SMS Toggle (allow bypass for test messages)
        $bypassGlobal = !empty($options['bypass_global']);
        if (!$bypassGlobal && !$this->isSettingActive('global_sms_enabled', true)) {
            return $this->logBlocked($recipient, $message, $type, 'Global SMS sending is disabled in settings.', $options);
        }

        // 2. Validate Bangladeshi Phone Format
        if (!preg_match('/^8801[3-9]\d{8}$/', $recipient)) {
            return $this->logBlocked($recipient, $message, $type, 'Invalid recipient mobile number format.', $options);
        }

        // 3. Blacklist Check (Number, IP & Spam)
        try {
            if (class_exists(SmsBlacklist::class)) {
                if (method_exists(SmsBlacklist::class, 'isPhoneBlocked') && SmsBlacklist::isPhoneBlocked($recipient)) {
                    return $this->logBlocked($recipient, $message, $type, 'Recipient number is blacklisted.', $options);
                }

                if (!empty($options['ip_address']) && method_exists(SmsBlacklist::class, 'isIpBlocked') && SmsBlacklist::isIpBlocked($options['ip_address'])) {
                    return $this->logBlocked($recipient, $message, $type, 'Request IP address is blacklisted.', $options);
                }

                if (method_exists(SmsBlacklist::class, 'containsSpamWords') && SmsBlacklist::containsSpamWords($message)) {
                    return $this->logBlocked($recipient, $message, $type, 'Message contains blacklisted spam keywords.', $options);
                }
            }
        } catch (Throwable $e) {
            Log::warning('SmsBlacklist check exception bypassed: ' . $e->getMessage());
        }

        // 4. Marketing Permission / Unsubscribe Check
        if ($type === 'marketing') {
            if (!$this->isSettingActive('sms_marketing_enabled', true)) {
                return $this->logBlocked($recipient, $message, $type, 'SMS Marketing is globally disabled.', $options);
            }

            try {
                if (class_exists(CommunicationPreference::class) && method_exists(CommunicationPreference::class, 'canReceiveMarketingSms')) {
                    if (!CommunicationPreference::canReceiveMarketingSms($recipient)) {
                        return $this->logBlocked($recipient, $message, $type, 'Recipient unsubscribed from SMS marketing.', $options);
                    }
                }
            } catch (Throwable $e) {
                Log::warning('CommunicationPreference check bypassed: ' . $e->getMessage());
            }
        }

        // 5. Calculate Message Parts & Estimated Cost
        $metrics = $this->analyzeMessage($message);
        $costPerSms = $this->provider ? (float) $this->provider->price_per_sms : 0.3500;
        $totalCost = $metrics['sms_parts'] * $costPerSms;

        // Clean sender ID
        $rawSenderId = trim((string) ($options['sender_id'] ?? $this->provider?->sender_id ?? ''));
        $senderId = (!empty($rawSenderId) && strtolower($rawSenderId) !== 'default') ? $rawSenderId : null;

        // 6. Dispatch via OnecodesoftDriver
        $response = $this->driver->sendSingle($recipient, $message, $senderId);
        $isSuccess = (bool) ($response['status'] ?? false);

        // 7. REAL-TIME AUTOMATIC BALANCE UPDATE
        if ($isSuccess && $this->provider) {
            $rawGatewayBal = $response['data']['data']['balance']
                ?? $response['data']['balance']
                ?? $response['balance']
                ?? null;

            if ($rawGatewayBal !== null) {
                // If API returned live balance in response
                $newBal = (float) $rawGatewayBal;
                $unitPrice = $this->provider->price_per_sms > 0 ? (float) $this->provider->price_per_sms : 0.35;
                $this->provider->update([
                    'balance'                => $newBal,
                    'estimated_sms'          => (int) floor($newBal / $unitPrice),
                    'last_balance_synced_at' => Carbon::now(),
                ]);
            } else {
                // Deduct cost locally in real-time so DB balance decrements immediately
                $unitPrice = $this->provider->price_per_sms > 0 ? (float) $this->provider->price_per_sms : 0.35;
                $currentBal = (float) $this->provider->balance;
                $newBal = max(0.00, round($currentBal - $totalCost, 4));

                $this->provider->update([
                    'balance'                => $newBal,
                    'estimated_sms'          => (int) floor($newBal / $unitPrice),
                    'last_balance_synced_at' => Carbon::now(),
                ]);

                // Query live gateway balance to reconcile without throwing errors
                if (empty($options['skip_live_sync'])) {
                    try {
                        $this->syncBalance();
                    } catch (Throwable $syncEx) {
                        // Keep instant locally deducted balance
                    }
                }
            }
        }

        // 8. Record SMS Log safely
        $logId = null;
        try {
            $smsLog = SmsLog::create([
                'recipient'        => $recipient,
                'user_id'          => $options['user_id'] ?? null,
                'order_id'         => $options['order_id'] ?? null,
                'campaign_id'      => $options['campaign_id'] ?? null,
                'sms_type'         => $type,
                'provider'         => $this->provider?->driver ?? 'onecodesoft',
                'sender_id'        => $senderId ?: 'Default',
                'message_id'       => $response['message_id'] ?? null,
                'message_content'  => $message,
                'characters_count' => $metrics['characters_count'],
                'sms_parts'        => $metrics['sms_parts'],
                'cost'             => $totalCost,
                'status'           => $isSuccess ? 'sent' : 'failed',
                'status_code'      => $isSuccess ? '200' : '422',
                'gateway_response' => json_encode($response['data'] ?? $response),
                'is_sandbox'       => (bool) ($this->provider?->sandbox_mode ?? false),
                'sent_at'          => $isSuccess ? Carbon::now() : null,
            ]);
            $logId = $smsLog->id;
        } catch (Throwable $e) {
            Log::warning('SmsLog write failed: ' . $e->getMessage());
        }

        return [
            'status'     => $isSuccess,
            'message'    => $response['message'] ?? ($isSuccess ? 'Sent successfully' : 'Failed to send SMS'),
            'message_id' => $response['message_id'] ?? null,
            'log_id'     => $logId,
            'parts'      => $metrics['sms_parts'],
            'cost'       => $totalCost,
        ];
    }

    /**
     * Alias method for send to ensure compatibility across all callers
     */
    public function sendSingle(string $number, string $message, string $type = 'transactional', array $options = []): array
    {
        return $this->send($number, $message, $type, $options);
    }

    /**
     * Send bulk personalized messages via gateway driver with auto balance sync
     */
    public function sendBulk(array $messageParameters, ?string $senderId = null): array
    {
        $cleanSenderId = (!empty($senderId) && strtolower(trim($senderId)) !== 'default') ? trim($senderId) : null;
        $response = $this->driver->sendBulk($messageParameters, $cleanSenderId);

        // Auto sync live balance immediately after bulk dispatch
        if ($this->provider) {
            try {
                $this->syncBalance();
            } catch (Throwable $e) {}
        }

        return $response;
    }

    /**
     * Dispatch SMS by dynamic template key with automatic variable binding
     */
    public function sendTemplate(string $templateKey, string $number, array $variables = [], array $options = []): array
    {
        $normalizedKey = strtolower(trim($templateKey));

        // 1. Bypass switch check if explicitly requested (manual status trigger)
        $bypassSetting = !empty($options['bypass_setting']) || !empty($options['bypass_global']);

        // 2. Strict switch check: If turned OFF in settings, STOP immediately!
        if (!$bypassSetting && !$this->isTemplateNotificationEnabled($normalizedKey)) {
            return [
                'status'   => false,
                'disabled' => true,
                'message'  => "Automatic SMS notification for '{$templateKey}' is disabled in settings.",
            ];
        }

        // 3. Multi-alias database template lookup for exact event ONLY
        $templateAliasMap = [
            'order_pending'            => ['order_pending', 'order_placed_customer', 'order_placed', 'pending'],
            'order_placed'             => ['order_placed_customer', 'order_placed', 'order_confirmation', 'order_created'],
            'order_received'           => ['order_received', 'received'],
            'order_confirmed'          => ['order_confirmed', 'confirmed'],
            'order_processing'         => ['order_processing', 'order_packed', 'processing', 'packed'],
            'order_shipped'            => ['order_shipped', 'order_dispatch', 'shipped'],
            'order_attempt_to_deliver' => ['order_attempt_to_deliver', 'attempt_to_deliver', 'attempted'],
            'order_delivered'          => ['order_delivered', 'order_complete', 'delivered', 'completed'],
            'order_exchange'           => ['order_exchange', 'exchange', 'exchanged'],
            'order_returned'           => ['order_returned', 'returned', 'return'],
            'order_cancelled'          => ['order_cancelled', 'cancelled', 'order_cancel', 'cancel'],
            'customer_welcome'         => ['customer_welcome', 'welcome', 'user_welcome'],
        ];

        $candidateKeys = $templateAliasMap[$normalizedKey] ?? [$normalizedKey];

        $template = null;
        if (class_exists(SmsTemplate::class) && Schema::hasTable('sms_templates')) {
            $hasKeyCol  = Schema::hasColumn('sms_templates', 'key');
            $hasSlugCol = Schema::hasColumn('sms_templates', 'slug');

            $template = SmsTemplate::where('is_active', true)
                ->where(function ($sub) use ($candidateKeys, $hasKeyCol, $hasSlugCol) {
                    if ($hasKeyCol) {
                        $sub->whereIn('key', $candidateKeys);
                    }
                    if ($hasSlugCol) {
                        $sub->orWhereIn('slug', $candidateKeys);
                    }
                })
                ->first();
        }

        // Global fallback tags
        $defaultVariables = [
            'store_name'    => config('app.name', 'SHOP NOW BD'),
            'website_url'   => url('/'),
            'customer_name' => 'Valued Customer',
            'order_id'      => '',
            'invoice_no'    => '',
            'order_total'   => '0.00',
            'order_status'  => strtoupper(str_replace('_', ' ', $normalizedKey)),
            'courier_name'  => 'Courier',
            'tracking_code' => '',
        ];
        $allVariables = array_merge($defaultVariables, $variables);

        // 4. Safe message rendering (Uses DB template if found, else uses fallback templates so SMS NEVER drops)
        if ($template) {
            if (method_exists($template, 'render')) {
                $message = $template->render($allVariables);
            } else {
                $rawBody = (string) ($template->body ?? ($template->message ?? ($template->content ?? '')));
                $message = $rawBody;
                foreach ($allVariables as $key => $val) {
                    $message = str_replace(["{{$key}}", "{{ {$key} }}"], (string) $val, $message);
                }
            }
        } else {
            // Intelligent Built-in Fallbacks for all 10 Statuses
            $fallbackBodies = [
                'order_pending'            => "Dear {customer_name}, your order #{invoice_no} is currently pending review. Thank you for shopping with {store_name}!",
                'order_placed'             => "Dear {customer_name}, your order #{invoice_no} of ৳{order_total} has been placed successfully. Thank you for shopping with {store_name}!",
                'order_received'           => "Dear {customer_name}, your order #{invoice_no} has been received by {store_name} and is queued for verification.",
                'order_confirmed'          => "Dear {customer_name}, your order #{invoice_no} has been confirmed and is being processed. - {store_name}",
                'order_processing'         => "Dear {customer_name}, your order #{invoice_no} is currently being packed. - {store_name}",
                'order_shipped'            => "Dear {customer_name}, your order #{invoice_no} has been dispatched via {courier_name}. Tracking ID: {tracking_code}. - {store_name}",
                'order_attempt_to_deliver' => "Dear {customer_name}, delivery was attempted for order #{invoice_no} via {courier_name}. Please keep your phone reachable. - {store_name}",
                'order_delivered'          => "Dear {customer_name}, your order #{invoice_no} has been delivered successfully. Thank you for choosing {store_name}!",
                'order_exchange'           => "Dear {customer_name}, the exchange request for order #{invoice_no} has been processed. - {store_name}",
                'order_returned'           => "Dear {customer_name}, your order #{invoice_no} has been marked as returned. If you have questions, please contact {store_name}.",
                'order_cancelled'          => "Dear {customer_name}, your order #{invoice_no} has been cancelled. Please contact {store_name} for any inquiries.",
                'customer_welcome'         => "Welcome to {store_name}! Your account has been registered successfully.",
            ];

            $message = $fallbackBodies[$normalizedKey] ?? "Order Update: Your order #{invoice_no} status is now {order_status}. - {store_name}";
            foreach ($allVariables as $key => $val) {
                $message = str_replace(["{{$key}}", "{{ {$key} }}"], (string) $val, $message);
            }
        }

        $type = match ($template?->event_type ?? 'transactional') {
            'auth'      => 'otp',
            'marketing' => 'marketing',
            default     => 'transactional',
        };

        if (!empty($template?->sender_id) && empty($options['sender_id'])) {
            $options['sender_id'] = $template->sender_id;
        }

        return $this->send($number, $message, $type, $options);
    }

    /**
     * Check if a specific template notification switch is enabled with alias support across all 10 order statuses
     */
    protected function isTemplateNotificationEnabled(string $templateKey): bool
    {
        $statusKeyMap = [
            'order_pending'            => ['auto_sms_order_pending', 'auto_sms_order_placed_customer'],
            'order_placed'             => ['auto_sms_order_placed_customer', 'auto_sms_order_placed'],
            'order_received'           => ['auto_sms_order_received'],
            'order_confirmed'          => ['auto_sms_order_confirmed'],
            'order_processing'         => ['auto_sms_order_processing', 'auto_sms_order_packed'],
            'order_shipped'            => ['auto_sms_order_shipped'],
            'order_attempt_to_deliver' => ['auto_sms_order_attempt_to_deliver'],
            'order_delivered'          => ['auto_sms_order_delivered'],
            'order_exchange'           => ['auto_sms_order_exchange'],
            'order_returned'           => ['auto_sms_order_returned'],
            'order_cancelled'          => ['auto_sms_order_cancelled'],
            'customer_welcome'         => ['auto_sms_customer_welcome'],
        ];

        $keysToCheck = $statusKeyMap[$templateKey] ?? ["auto_sms_{$templateKey}"];

        foreach ($keysToCheck as $k) {
            if (Schema::hasTable('sms_settings')) {
                $row = SmsSetting::where('key', $k)->first();
                if ($row !== null) {
                    $val = strtolower(trim((string) $row->value));
                    return !in_array($val, ['0', 'false', 'off', 'no'], true);
                }
            }
        }

        foreach ($keysToCheck as $k) {
            if ($this->isSettingActive($k, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Safe setting status checker with database alias fallback
     */
    protected function isSettingActive(string $key, bool $default = true): bool
    {
        try {
            if (Schema::hasTable('sms_settings')) {
                $row = SmsSetting::where('key', $key)->first();
                if ($row !== null) {
                    $val = strtolower(trim((string) $row->value));
                    return !in_array($val, ['0', 'false', 'off', 'no'], true);
                }
            }

            if (class_exists(SmsSetting::class) && method_exists(SmsSetting::class, 'isEnabled')) {
                try {
                    return (bool) SmsSetting::isEnabled($key, $default);
                } catch (Throwable $e) {}
            }
        } catch (Throwable $e) {
            return $default;
        }

        return $default;
    }

    /**
     * Helper to log rejected/blocked outgoing attempts
     */
    protected function logBlocked(string $recipient, string $message, string $type, string $reason, array $options): array
    {
        $metrics = $this->analyzeMessage($message);
        $logId = null;

        try {
            $smsLog = SmsLog::create([
                'recipient'        => $recipient,
                'user_id'          => $options['user_id'] ?? null,
                'order_id'         => $options['order_id'] ?? null,
                'campaign_id'      => $options['campaign_id'] ?? null,
                'sms_type'         => $type,
                'provider'         => $this->provider?->driver ?? 'onecodesoft',
                'sender_id'        => $options['sender_id'] ?? 'Default',
                'message_content'  => $message,
                'characters_count' => $metrics['characters_count'],
                'sms_parts'        => $metrics['sms_parts'],
                'cost'             => 0.0000,
                'status'           => 'blocked',
                'status_code'      => 'BLOCKED',
                'gateway_response' => json_encode(['error' => $reason]),
                'is_sandbox'       => (bool) ($this->provider?->sandbox_mode ?? false),
            ]);
            $logId = $smsLog->id;
        } catch (Throwable $e) {
            Log::warning('SmsLog block log write failed: ' . $e->getMessage());
        }

        return [
            'status'  => false,
            'message' => $reason,
            'log_id'  => $logId,
        ];
    }

    /**
     * Query gateway balance, sync to database provider record, and refresh
     */
    public function syncBalance(): array
    {
        $result = $this->driver->checkBalance();

        if (!empty($result['status']) && isset($result['balance']) && $this->provider) {
            $bal = (float) $result['balance'];
            $unitPrice = $this->provider->price_per_sms > 0 ? (float) $this->provider->price_per_sms : 0.35;
            $this->provider->update([
                'balance'                => $bal,
                'estimated_sms'          => (int) floor($bal / $unitPrice),
                'last_balance_synced_at' => Carbon::now(),
            ]);
            $this->provider->refresh();
        }

        return $result;
    }

    /**
     * Alias for syncBalance
     */
    public function checkBalance(): array
    {
        return $this->syncBalance();
    }

    /**
     * Check delivery status report (DLR) for sent message ID
     */
    public function checkDeliveryStatus(string $messageId): array
    {
        return $this->driver->checkDeliveryStatus($messageId);
    }

    /**
     * Managed Identity OTP Request
     */
    public function requestIdentityOtp(string $number): array
    {
        return $this->driver->requestIdentityOtp($number);
    }

    /**
     * Managed Identity OTP Verification
     */
    public function verifyIdentityOtp(string $number, string $code): array
    {
        return $this->driver->verifyIdentityOtp($number, $code);
    }

    /**
     * Send custom generated OTP
     */
    public function sendCustomOtp(string $number, string $message): array
    {
        return $this->driver->sendCustomOtp($number, $message);
    }
}