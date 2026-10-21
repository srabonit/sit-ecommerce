<?php

namespace App\Services;

use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send Dynamic SMS using DB credentials
     */
    public static function sendSms(string $phone, string $message): bool
    {
        $setting = NotificationSetting::where('type', 'sms')->where('is_active', true)->first();

        if (!$setting) {
            return false;
        }

        $credentials = $setting->credentials;

        // Greenweb SMS Gateway Implementation
        if ($setting->provider === 'greenweb') {
            $response = Http::post('https://api.greenweb.com.bd/api.php', [
                'token' => $credentials['api_token'] ?? '',
                'to'    => $phone,
                'message' => $message
            ]);

            return $response->successful();
        }

        // Generic / Custom Webhook SMS Integration
        if (isset($credentials['api_url'])) {
            Http::post($credentials['api_url'], [
                'api_key' => $credentials['api_key'] ?? '',
                'to'      => $phone,
                'msg'     => $message
            ]);
            return true;
        }

        return false;
    }

    /**
     * Configure Dynamic SMTP at Runtime & Send Email
     */
    public static function sendEmail(string $toEmail, string $subject, string $bodyContent): void
    {
        $setting = NotificationSetting::where('type', 'smtp')->where('is_active', true)->first();

        if ($setting) {
            $cred = $setting->credentials;

            // Dynamically override Laravel Mail Config from DB
            Config::set('mail.mailers.smtp.host', $cred['host'] ?? env('MAIL_HOST'));
            Config::set('mail.mailers.smtp.port', $cred['port'] ?? env('MAIL_PORT'));
            Config::set('mail.mailers.smtp.username', $cred['username'] ?? env('MAIL_USERNAME'));
            Config::set('mail.mailers.smtp.password', $cred['password'] ?? env('MAIL_PASSWORD'));
            Config::set('mail.mailers.smtp.encryption', $cred['encryption'] ?? 'tls');
            Config::set('mail.from.address', $cred['from_address'] ?? env('MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', $cred['from_name'] ?? config('app.name'));
        }

        Mail::raw($bodyContent, function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)->subject($subject);
        });
    }
}