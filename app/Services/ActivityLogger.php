<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class ActivityLogger
{
    /**
     * যে সকল সংবেদনশীল ফিল্ড কখনোই অডিট লগে সেভ হবে না
     */
    protected static array $blacklistedFields = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'secret',
        'api_key',
        'api_secret',
        'card_number',
        'cvv',
        'otp',
    ];

    /**
     * সেন্ট্রাল অ্যাক্টিভিটি লগ রেকর্ড মেথড
     *
     * @param string $action (login, logout, create, update, delete, status_change, etc.)
     * @param string $module (orders, products, courier, users, etc.)
     * @param string $description (লগের সংক্ষিপ্ত বিবরণ)
     * @param array $options (record_id, record_ref, old_values, new_values, result, etc.)
     * @return void
     */
    public static function log(string $action, string $module, string $description, array $options = []): void
    {
        try {
            if (!Schema::hasTable('activity_logs')) {
                return;
            }

            $user = Auth::user();

            // কাস্টমারদের কার্যকলাপ বাদ দিয়ে কেবল ইন্টারনাল কর্মীদের লগ রাখা হবে
            if ($user && method_exists($user, 'isCustomer') && $user->isCustomer()) {
                return;
            }

            $userId   = $options['user_id'] ?? ($user ? $user->id : null);
            $userName = $options['user_name'] ?? ($user ? $user->name : 'System/Guest');
            $role     = $options['role'] ?? ($user ? $user->role : 'system');
            $branchId = $options['branch_id'] ?? ($user ? $user->branch_id : null);

            $oldValues = isset($options['old_values']) ? self::cleanSensitiveData($options['old_values']) : null;
            $newValues = isset($options['new_values']) ? self::cleanSensitiveData($options['new_values']) : null;

            $userAgent = Request::header('User-Agent');
            $deviceType = self::detectDeviceType($userAgent);

            DB::table('activity_logs')->insert([
                'user_id'     => $userId,
                'user_name'   => $userName,
                'role'        => $role,
                'branch_id'   => $branchId,
                'action'      => strtolower(trim($action)),
                'module'      => strtolower(trim($module)),
                'record_type' => $options['record_type'] ?? null,
                'record_id'   => isset($options['record_id']) ? (string)$options['record_id'] : null,
                'record_ref'  => isset($options['record_ref']) ? (string)$options['record_ref'] : null,
                'description' => $description,
                'old_values'  => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'  => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip_address'  => Request::ip(),
                'user_agent'  => $userAgent ? substr($userAgent, 0, 500) : null,
                'device_type' => $deviceType,
                'result'      => strtolower($options['result'] ?? 'success'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // অডিট লগে ত্রুটি দেখা দিলে মূল অপারেশন বন্ধ হবে না
            report($e);
        }
    }

    /**
     * সংবেদনশীল তথ্য মাস্কিং ফিল্টার
     */
    protected static function cleanSensitiveData(mixed $data): mixed
    {
        if (is_object($data)) {
            $data = (array)$data;
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $cleanKey = strtolower(trim((string)$key));

            if (in_array($cleanKey, self::$blacklistedFields)) {
                $data[$key] = '******** (HIDDEN)';
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $data[$key] = self::cleanSensitiveData($value);
            }
        }

        return $data;
    }

    /**
     * ইউজার এজেন্ট থেকে ডিভাইস টাইপ নির্ণয়
     */
    protected static function detectDeviceType(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            return 'Tablet';
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            return 'Mobile';
        }

        return 'Desktop';
    }
}