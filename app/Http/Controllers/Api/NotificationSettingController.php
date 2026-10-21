<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function getSettings(): JsonResponse
    {
        $settings = NotificationSetting::all();
        return response()->json(['status' => true, 'data' => $settings]);
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $request->validate([
            'type'        => 'required|in:smtp,sms',
            'provider'    => 'required|string',
            'credentials' => 'required|array',
            'is_active'   => 'required|boolean',
        ]);

        $setting = NotificationSetting::updateOrCreate(
            ['type' => $request->type],
            [
                'provider'    => $request->provider,
                'credentials' => $request->credentials,
                'is_active'   => $request->is_active,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => ucfirst($request->type) . ' settings updated successfully.',
            'data'    => $setting
        ]);
    }
}