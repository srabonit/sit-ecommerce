<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ManualPaymentSettingRequest;
use App\Http\Requests\Payment\PaymentGatewaySettingRequest;
use App\Models\ManualPaymentMethod;
use App\Models\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentSettingController extends Controller
{
    /**
     * Get Gateway Credentials & Status (Admin View)
     */
    public function getGateways(): JsonResponse
    {
        $gateways = PaymentGateway::all();

        return response()->json([
            'status' => true,
            'data'   => $gateways
        ], 200);
    }

    /**
     * Save/Update Payment Gateway Credentials
     */
    public function saveGateway(PaymentGatewaySettingRequest $request): JsonResponse
    {
        $gateway = PaymentGateway::updateOrCreate(
            ['code' => $request->code],
            [
                'name'        => $request->name,
                'credentials' => $request->credentials,
                'is_live'     => $request->is_live,
                'is_active'   => $request->is_active,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => "{$gateway->name} configuration saved successfully.",
            'data'    => $gateway
        ], 200);
    }

    /**
     * Get Active Manual Payment Options
     */
    public function getManualMethods(): JsonResponse
    {
        $methods = ManualPaymentMethod::all();

        return response()->json([
            'status' => true,
            'data'   => $methods
        ], 200);
    }

    /**
     * Save/Update Manual Payment Option
     */
    public function saveManualMethod(ManualPaymentSettingRequest $request, $id = null): JsonResponse
    {
        $method = ManualPaymentMethod::updateOrCreate(
            ['id' => $id],
            [
                'name'           => $request->name,
                'account_number' => $request->account_number,
                'instruction'    => $request->instruction,
                'is_active'      => $request->is_active,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => "Manual payment option saved successfully.",
            'data'    => $method
        ], 200);
    }
}