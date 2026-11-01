<?php

namespace App\Services\Security;

use App\Models\SecurityAlert;
use App\Models\SecuritySetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityNotificationService
{
    /**
     * ডিফল্ট ইমেইল কুলডাউন সময় (মিনিট) — একই আইপি ও ইভেন্টের পরবর্তী ইমেইলের বিরতি
     */
    const DEFAULT_EMAIL_COOLDOWN_MINUTES = 30;

    /**
     * সিকিউরিটি অ্যালার্ট প্রসেস ও নোটিফিকেশন ট্রিগার করা
     */
    public function dispatchAlert(SecurityAlert $alert): void
    {
        // মাস্টার নোটিফিকেশন বন্ধ থাকলে রিটার্ন
        if (!SecuritySetting::isEnabled('security_master_active', true) ||
            !SecuritySetting::isEnabled('security_alerts_active', true)) {
            return;
        }

        // ইমেইল নোটিফিকেশন শর্ত ও কুলডাউন যাচাই
        if ($this->shouldSendEmailNotification($alert)) {
            $this->sendEmailNotification($alert);
        }
    }

    /**
     * ইমেইল পাঠানো যাবে কি না তা নীতি ও কুলডাউন অনুযায়ী যাচাই
     */
    public function shouldSendEmailNotification(SecurityAlert $alert): bool
    {
        // ১. অ্যাডমিন সেটিংসে ইমেইল নোটিফিকেশন এনেবল কি না
        if (!SecuritySetting::isEnabled('security_email_notifications', true)) {
            return false;
        }

        // ২. ইতোমধ্যে এই অ্যালার্টের ইমেইল পাঠানো হয়ে থাকলে
        if ($alert->email_sent) {
            return false;
        }

        // ৩. রিস্ক লেভেল ফিল্টারিং (ডিফল্ট: High এবং Critical ইভেন্টে কেবল ইমেইল যাবে)
        $minEmailLevel = SecuritySetting::get('security_email_min_risk_level', 'high');
        $priority = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        $alertScore = $priority[$alert->risk_level] ?? 2;
        $requiredScore = $priority[$minEmailLevel] ?? 3;

        if ($alertScore < $requiredScore) {
            return false;
        }

        // ৪. অ্যান্টি-স্প্যাম কুলডাউন লক (Anti-Spam Email Throttling Lock)
        // একই আইপি এবং একই ইভেন্টের জন্য নির্ধারিত সময়ে সর্বোচ্চ ১টি ইমেইল পাঠানো হবে
        $ip = $alert->ip_address ?: 'general';
        $cooldownKey = "sec_mail_cooldown_{$alert->event_type}_{$ip}";

        if (Cache::has($cooldownKey)) {
            // কুলডাউনে থাকলে মেটাডাটাতে নোট রেখে দেওয়া হবে
            $metadata = $alert->metadata ?? [];
            $metadata['email_throttled'] = true;
            $metadata['last_throttled_at'] = Carbon::now()->toDateTimeString();
            $alert->update(['metadata' => $metadata]);

            return false;
        }

        return true;
    }

    /**
     * অ্যাডমিনকে ব্যাকগ্রাউন্ডে নিরাপদ ইমেইল প্রেরণ
     */
    public function sendEmailNotification(SecurityAlert $alert): bool
    {
        $adminEmail = $this->getRecipientEmail();

        if (empty($adminEmail)) {
            Log::channel('single')->warning("Security Alert Email Skipped: No recipient admin email found.");
            return false;
        }

        $ip = $alert->ip_address ?: 'general';
        $cooldownMinutes = SecuritySetting::getInt('security_email_cooldown_minutes', self::DEFAULT_EMAIL_COOLDOWN_MINUTES);
        $cooldownKey = "sec_mail_cooldown_{$alert->event_type}_{$ip}";

        try {
            // ইমেইল ডাটা পেলোড প্রস্তুত
            $mailData = [
                'alert_id' => $alert->id,
                'event_type' => $alert->event_type,
                'title' => $alert->title,
                'message' => $alert->message,
                'risk_level' => strtoupper($alert->risk_level),
                'ip_address' => $alert->ip_address ?? 'N/A',
                'affected_url' => $alert->affected_url ?? 'N/A',
                'attempt_count' => $alert->attempt_count,
                'detected_at' => $alert->created_at->format('Y-m-d H:i:s'),
                'security_dashboard_url' => url('/admin/security/alerts'),
            ];

            // লারাভেলের মেইল সার্ভিস কল (মেইলেবল তৈরি হলে এটি এমবেড হবে; অন্যথায় স্ট্যান্ডার্ড ভিউ/টেক্সট ফলব্যাক)
            Mail::send([], [], function ($message) use ($adminEmail, $alert, $mailData) {
                $message->to($adminEmail)
                    ->subject("[Security Alert: {$mailData['risk_level']}] {$alert->title}")
                    ->html($this->generateEmailTemplateHtml($mailData));
            });

            // ইমেইল স্ট্যাটাস ও কুলডাউন লক সক্রিয় করা
            $alert->update([
                'email_sent' => true,
                'email_sent_at' => Carbon::now(),
            ]);

            Cache::put($cooldownKey, true, $cooldownMinutes * 60);

            return true;
        } catch (\Throwable $e) {
            Log::channel('single')->error("Security Alert Email Failed: " . $e->getMessage(), [
                'alert_id' => $alert->id,
                'admin_email' => $adminEmail,
            ]);

            return false;
        }
    }

    /**
     * অ্যাডমিন ড্যাশবোর্ডের হেডারে অপঠিত অ্যালার্ট কাউন্টার লোড করা (ক্যাশে অপ্টিমাইজড)
     */
    public function getUnreadAlertCount(): int
    {
        return Cache::remember('sec_unread_alerts_badge_count', 60, function () {
            return SecurityAlert::unread()->count();
        });
    }

    /**
     * প্রাপক অ্যাডমিন ইমেইল সংগ্রহ
     */
    protected function getRecipientEmail(): ?string
    {
        $customEmail = SecuritySetting::get('security_alert_notification_email');
        if (!empty($customEmail) && filter_var($customEmail, FILTER_VALIDATE_EMAIL)) {
            return trim($customEmail);
        }

        // ডিফল্ট: প্রথম সুপার অ্যাডমিনের ইমেইল
        $admin = User::whereIn('role', ['admin', 'super_admin'])->first();
        return $admin ? $admin->email : config('mail.from.address');
    }

    /**
     * স্ট্যান্ডার্ড ইনলাইন এইচটিএমএল ইমেইল টেমপ্লেট
     */
    protected function generateEmailTemplateHtml(array $data): string
    {
        $riskColor = match (strtolower($data['risk_level'])) {
            'critical' => '#dc2626',
            'high' => '#ea580c',
            'medium' => '#d97706',
            default => '#2563eb',
        };

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="font-family: Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px;">
            <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden;">
                <div style="background-color: {$riskColor}; padding: 16px 24px; color: #ffffff;">
                    <h2 style="margin: 0; font-size: 20px;">{$data['risk_level']} Security Alert</h2>
                    <p style="margin: 5px 0 0; font-size: 14px; opacity: 0.9;">Laravel E-Commerce & POS Security Protection</p>
                </div>
                <div style="padding: 24px; color: #1f2937;">
                    <h3 style="margin-top: 0; color: #111827;">{$data['title']}</h3>
                    <p style="font-size: 15px; line-height: 1.5; color: #4b5563;">{$data['message']}</p>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px;">
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-weight: bold; width: 140px;">IP Address:</td><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; color: #1f2937;">{$data['ip_address']}</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-weight: bold;">Attempt Count:</td><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; color: #1f2937;">{$data['attempt_count']}</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-weight: bold;">Affected URL:</td><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; color: #1f2937; word-break: break-all;">{$data['affected_url']}</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-weight: bold;">Detected Time:</td><td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; color: #1f2937;">{$data['detected_at']}</td></tr>
                    </table>
                    <div style="margin-top: 25px; text-align: center;">
                        <a href="{$data['security_dashboard_url']}" style="background-color: #1f2937; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: bold; display: inline-block;">View Security Incident Details</a>
                    </div>
                </div>
                <div style="background-color: #f9fafb; padding: 12px 24px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;">
                    Anti-Spam Throttling Active: You will not receive duplicate emails for this IP for the next {$this->getCooldownMinutes()} minutes.
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    protected function getCooldownMinutes(): int
    {
        return SecuritySetting::getInt('security_email_cooldown_minutes', self::DEFAULT_EMAIL_COOLDOWN_MINUTES);
    }
}