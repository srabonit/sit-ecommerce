<?php

namespace App\Services\Sms\Drivers;

use App\Models\SmsProvider;
use App\Services\Sms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OnecodesoftDriver implements SmsProviderInterface
{
    protected SmsProvider $provider;
    protected string $baseUrl;
    protected ?string $apiToken;
    protected bool $isSandbox;

    public function __construct(?SmsProvider $provider = null)
    {
        $this->provider = $provider ?? SmsProvider::where('driver', 'onecodesoft')
            ->where('is_active', true)
            ->first()
            ?? SmsProvider::where('driver', 'onecodesoft')->first()
            ?? new SmsProvider();

        // Base URL normalization: handles with or without trailing slash and /api
        $rawBase = trim((string) ($this->provider->base_url ?: 'https://sms.ocs-api.top/api'));
        $this->baseUrl = rtrim($rawBase, '/');

        $this->apiToken = $this->provider->decrypted_token ?: $this->provider->api_token;
        $this->isSandbox = (bool) $this->provider->sandbox_mode;
    }

    /**
     * Standard HTTP client configured with Bearer token and SSL bypass for Localhost/XAMPP
     */
    protected function client()
    {
        return Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . trim((string) $this->apiToken),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->timeout(20);
    }

    /**
     * Build precise v3 endpoint path supporting sandbox twins
     */
    protected function getEndpoint(string $path, bool $isSendEndpoint = false): string
    {
        $base = $this->baseUrl;

        // Ensure base ends with /api before version prefix
        if (!str_contains($base, '/api') && !str_contains($base, '/v3')) {
            $base .= '/api';
        }

        // Resolve prefix for sandbox vs live
        $versionPrefix = ($this->isSandbox && $isSendEndpoint) ? '/v3/sandbox' : '/v3';

        if (str_ends_with($base, '/api')) {
            return $base . $versionPrefix . '/' . ltrim($path, '/');
        }

        if (str_contains($base, '/v3')) {
            return preg_replace('#/v3.*#', $versionPrefix, $base) . '/' . ltrim($path, '/');
        }

        return $base . $versionPrefix . '/' . ltrim($path, '/');
    }

    /**
     * Format Bangladeshi numbers to international standard 8801XXXXXXXXX
     */
    public function formatNumber(string $number): string
    {
        $clean = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($clean, '01') && strlen($clean) === 11) {
            return '88' . $clean;
        }

        if (str_starts_with($clean, '1') && strlen($clean) === 10) {
            return '880' . $clean;
        }

        return $clean;
    }

    /**
     * Send single transactional or notification SMS (POST /api/v3/send-sms)
     */
    public function sendSingle(string $number, string $message, ?string $senderId = null): array
    {
        $formattedNumber = $this->formatNumber($number);

        $payload = [
            'number'  => $formattedNumber,
            'message' => $message,
        ];

        // senderid is optional in v3. Omit if empty or default so gateway auto-selects.
        $cleanSenderId = trim((string) ($senderId ?: $this->provider->sender_id));
        if (!empty($cleanSenderId) && strtolower($cleanSenderId) !== 'default') {
            $payload['senderid'] = $cleanSenderId;
        }

        try {
            $url = $this->getEndpoint('send-sms', true);
            $response = $this->client()->post($url, $payload);
            $result = $response->json() ?? [];

            if (($result['status'] ?? '') === 'success') {
                $data = $result['data'] ?? [];
                $firstResult = $data['results'][0] ?? [];
                $recipientStatus = strtolower($firstResult['status'] ?? '');
                $messageId = $firstResult['message_id'] ?? ($data['message_id'] ?? null);

                // Live accepted status is 'sent', or simulation outcome in sandbox
                if (in_array($recipientStatus, ['sent', 'success'], true) || !empty($firstResult['would_be'])) {
                    return [
                        'status'     => true,
                        'message'    => $result['message'] ?? 'SMS sent successfully.',
                        'message_id' => $messageId ?: ('SIM-' . uniqid()),
                        'cost'       => (float) ($data['total_cost'] ?? 0.3500),
                        'parts'      => (int) ($firstResult['sms_count'] ?? 1),
                        'data'       => $result,
                    ];
                }

                return [
                    'status'  => false,
                    'message' => "Recipient {$recipientStatus}: " . ($firstResult['code'] ?? $result['message'] ?? 'Delivery rejected'),
                    'data'    => $result,
                ];
            }

            return [
                'status'  => false,
                'message' => $result['message'] ?? 'Failed to send SMS via Onecodesoft.',
                'data'    => $result,
            ];
        } catch (Throwable $e) {
            Log::error('OnecodesoftDriver sendSingle Exception: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Send bulk personalized messages (POST /api/v3/send-bulk-sms)
     */
    public function sendBulk(array $messageParameters, ?string $senderId = null): array
    {
        $normalizedBatch = array_map(function ($item) {
            return [
                'Number' => $this->formatNumber($item['Number'] ?? $item['number'] ?? ''),
                'Text'   => $item['Text'] ?? $item['text'] ?? $item['message'] ?? '',
            ];
        }, $messageParameters);

        $payload = [
            'MessageParameters' => $normalizedBatch,
        ];

        $cleanSenderId = trim((string) ($senderId ?: $this->provider->sender_id));
        if (!empty($cleanSenderId) && strtolower($cleanSenderId) !== 'default') {
            $payload['senderid'] = $cleanSenderId;
        }

        try {
            $url = $this->getEndpoint('send-bulk-sms', true);
            $response = $this->client()->post($url, $payload);
            $result = $response->json() ?? [];

            if (($result['status'] ?? '') === 'success') {
                $data = $result['data'] ?? [];
                $firstResult = $data['results'][0] ?? [];

                return [
                    'status'     => true,
                    'message'    => $result['message'] ?? 'Bulk SMS request processed.',
                    'message_id' => $firstResult['message_id'] ?? ('BULK-' . uniqid()),
                    'total_cost' => (float) ($data['total_cost'] ?? 0),
                    'data'       => $result,
                ];
            }

            return [
                'status'  => false,
                'message' => $result['message'] ?? 'Bulk dispatch failed.',
                'data'    => $result,
            ];
        } catch (Throwable $e) {
            Log::error('OnecodesoftDriver sendBulk Exception: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Batch connection error: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Check gateway balance (GET /api/v3/balance)
     */
    public function checkBalance(): array
    {
        try {
            $url = $this->getEndpoint('balance', false);
            $response = $this->client()->get($url);
            $result = $response->json() ?? [];

            if (($result['status'] ?? '') === 'success') {
                $data = $result['data'] ?? [];
                $balance = (float) ($data['balance'] ?? 0.00);
                $estimatedSms = (int) ($data['estimate_sms'] ?? ($data['remaining_sms'] ?? 0));

                if ($this->provider->exists) {
                    $this->provider->update([
                        'balance'                => $balance,
                        'estimated_sms'          => $estimatedSms,
                        'last_balance_synced_at' => now(),
                        'last_connected_at'      => now(),
                    ]);
                }

                return [
                    'status'        => true,
                    'balance'       => $balance,
                    'estimated_sms' => $estimatedSms,
                    'currency'      => 'BDT',
                    'data'          => $result,
                ];
            }

            return [
                'status'  => false,
                'message' => $result['message'] ?? 'Unable to fetch balance from gateway.',
                'data'    => $result,
            ];
        } catch (Throwable $e) {
            Log::error('OnecodesoftDriver checkBalance Exception: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Balance query failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delivery status inquiry (GET /api/v3/status?message_id=...)
     */
    public function checkDeliveryStatus(string $messageId): array
    {
        if (str_starts_with($messageId, 'SANDBOX-') || str_starts_with($messageId, 'SIM-')) {
            return [
                'status'          => true,
                'delivery_status' => 'delivered',
                'message_id'      => $messageId,
            ];
        }

        try {
            $url = $this->getEndpoint('status', false);
            $response = $this->client()->get($url, [
                'message_id' => $messageId,
            ]);
            $result = $response->json() ?? [];

            if (($result['status'] ?? '') === 'success') {
                $dlr = $result['data']['Data'] ?? $result['data'] ?? [];
                $rawStatus = strtolower(trim((string) ($dlr['Status'] ?? 'sent')));

                return [
                    'status'          => true,
                    'delivery_status' => $rawStatus,
                    'data'            => $result,
                ];
            }

            return [
                'status'  => false,
                'message' => $result['message'] ?? 'Status check failed.',
                'data'    => $result,
            ];
        } catch (Throwable $e) {
            return [
                'status'  => false,
                'message' => 'Status error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Managed Identity OTP Request (POST /api/v3/auth)
     */
    public function requestIdentityOtp(string $number): array
    {
        $formattedNumber = $this->formatNumber($number);

        try {
            $url = $this->getEndpoint('auth', true);
            $response = $this->client()->post($url, [
                'number' => $formattedNumber,
            ]);
            $result = $response->json() ?? [];

            if (($result['status'] ?? '') === 'success') {
                $data = $result['data'] ?? [];
                return [
                    'status'          => true,
                    'message'         => $result['message'] ?? 'OTP dispatched.',
                    'provider_otp_id' => $data['otp_id'] ?? null,
                    'message_id'      => $data['message_id'] ?? null,
                    'next_retry_at'   => $data['next_retry_at'] ?? null,
                    'data'            => $result,
                ];
            }

            return [
                'status'  => false,
                'message' => $result['message'] ?? 'Could not request Identity OTP.',
                'data'    => $result,
            ];
        } catch (Throwable $e) {
            Log::error('OnecodesoftDriver requestIdentityOtp Exception: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Identity OTP request error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Managed Identity OTP Verification (POST /api/v3/auth/verify)
     */
    public function verifyIdentityOtp(string $number, string $code): array
    {
        $formattedNumber = $this->formatNumber($number);

        try {
            $url = $this->getEndpoint('auth/verify', true);
            $response = $this->client()->post($url, [
                'number' => $formattedNumber,
                'code'   => trim($code),
            ]);
            $result = $response->json() ?? [];

            if (($result['status'] ?? '') === 'success') {
                return [
                    'status'  => true,
                    'message' => $result['message'] ?? 'OTP verification confirmed.',
                    'data'    => $result['data'] ?? [],
                ];
            }

            return [
                'status'  => false,
                'message' => $result['message'] ?? 'Invalid or expired OTP.',
                'data'    => $result,
            ];
        } catch (Throwable $e) {
            Log::error('OnecodesoftDriver verifyIdentityOtp Exception: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'OTP verification connection error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dispatch Custom Generated OTP Text (POST /api/v3/otp)
     */
    public function sendCustomOtp(string $number, string $message): array
    {
        $formattedNumber = $this->formatNumber($number);

        try {
            $url = $this->getEndpoint('otp', true);
            $response = $this->client()->post($url, [
                'number'  => $formattedNumber,
                'message' => $message,
            ]);
            $result = $response->json() ?? [];

            if (($result['status'] ?? '') === 'success') {
                $data = $result['data'] ?? [];
                return [
                    'status'          => true,
                    'message'         => $result['message'] ?? 'OTP sent.',
                    'provider_otp_id' => $data['otp_id'] ?? null,
                    'message_id'      => $data['message_id'] ?? null,
                    'data'            => $result,
                ];
            }

            return [
                'status'  => false,
                'message' => $result['message'] ?? 'Custom OTP failed to send.',
                'data'    => $result,
            ];
        } catch (Throwable $e) {
            Log::error('OnecodesoftDriver sendCustomOtp Exception: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Custom OTP connection error: ' . $e->getMessage(),
            ];
        }
    }
}