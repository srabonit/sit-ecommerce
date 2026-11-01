<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class ActivityLogger
{
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
     * Flexible Central Activity Logger (Supports both 3-arg and 4-arg calls safely)
     */
    public static function log(string $action, mixed $module = 'system', mixed $description = '', array $options = []): void
    {
        try {
            if (!Schema::hasTable('activity_logs')) {
                return;
            }

            // Fix for TypeError: If 3 arguments passed: log($action, $description, $optionsArray)
            if (is_array($description) && empty($options)) {
                $options = $description;
                $description = is_string($module) ? $module : $action;
                $module = $options['module'] ?? 'general';
            } elseif (is_array($module)) {
                $options = $module;
                $description = $options['description'] ?? $action;
                $module = $options['module'] ?? 'general';
            } elseif (empty($description) && is_string($module)) {
                $description = $module;
                $module = 'general';
            }

            $user = Auth::user();

            if ($user && method_exists($user, 'isCustomer') && $user->isCustomer()) {
                return;
            }

            $userId   = $options['user_id'] ?? ($user ? $user->id : null);
            $userName = $options['user_name'] ?? ($user ? $user->name : 'System/Guest');
            $role     = $options['role'] ?? ($user ? $user->role : 'system');
            $branchId = $options['branch_id'] ?? ($user ? ($user->branch_id ?? null) : null);

            $oldValues = isset($options['old_values']) ? self::cleanSensitiveData($options['old_values']) : null;
            $newValues = isset($options['new_values']) ? self::cleanSensitiveData($options['new_values']) : null;

            $userAgent = Request::header('User-Agent');
            $deviceType = self::detectDeviceType($userAgent);

            DB::table('activity_logs')->insert([
                'user_id'     => $userId,
                'user_name'   => $userName,
                'role'        => $role,
                'branch_id'   => $branchId,
                'action'      => strtolower(trim((string)$action)),
                'module'      => strtolower(trim((string)$module)),
                'record_type' => $options['record_type'] ?? null,
                'record_id'   => isset($options['record_id']) ? (string)$options['record_id'] : null,
                'record_ref'  => isset($options['record_ref']) ? (string)$options['record_ref'] : null,
                'description' => (string)$description,
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
            report($e);
        }
    }

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