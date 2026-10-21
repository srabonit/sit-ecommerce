<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentSettingController extends Controller
{
    /**
     * Dynamic Public Web Root Detector (Localhost & cPanel Public_html Safe)
     */
    protected function getPublicRoot()
    {
        if (is_dir(base_path('../public_html'))) {
            return base_path('../public_html');
        }
        return isset($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT']) 
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') 
            : public_path();
    }

    /**
     * Universal File Upload Helper for QR Image (Directly saves to public/uploads/payment_qr)
     */
    protected function savePaymentFile($file, $subFolder = 'payment_qr')
    {
        $filename = 'qr_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $relativeDir = trim($subFolder, '/');

        $baseDir = $this->getPublicRoot() . '/uploads/' . $relativeDir;

        if (!file_exists($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $file->move($baseDir, $filename);

        return 'uploads/' . $relativeDir . '/' . $filename;
    }

    /**
     * Universal File Delete Helper for Payment QR
     */
    protected function deletePaymentFile($relativePath)
    {
        if (empty($relativePath) || filter_var($relativePath, FILTER_VALIDATE_URL)) return;

        $clean = ltrim($relativePath, '/');

        // Media Library theke select kora shared file disk theke delete kora jabe na
        if (str_starts_with($clean, 'uploads/media/') || str_starts_with($clean, 'media/')) {
            return;
        }

        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        $publicRoot = $this->getPublicRoot();
        $file1 = $publicRoot . '/' . $clean;
        $file2 = $publicRoot . '/storage/' . $clean;
        $file3 = storage_path('app/public/' . $clean);
        $file4 = $publicRoot . '/uploads/' . $clean;

        if (file_exists($file1)) @unlink($file1);
        if (file_exists($file2)) @unlink($file2);
        if (file_exists($file3)) @unlink($file3);
        if (file_exists($file4)) @unlink($file4);
    }

    /**
     * ডিসপ্লে পেমেন্ট গেটওয়ে সেটিংস (অটো সিডার সহ)
     */
    public function index()
    {
        // ১. যদি payment_gateways টেবিলে রেকর্ড না থাকে তবে অটোমেটিক সব গেটওয়ে সিড হবে
        $this->ensureDefaultGatewaysExist();

        $manualMethods = DB::table('manual_payment_methods')->latest('id')->get();
        $gateways = DB::table('payment_gateways')->orderBy('id', 'asc')->get();

        return view('admin.settings.payment_gateways', compact('manualMethods', 'gateways'));
    }

    /**
     * All in one gateway
     */
    private function ensureDefaultGatewaysExist()
    {
        $defaultGateways = [
            [
                'code' => 'bkash',
                'name' => 'bKash Payment',
                'credentials' => json_encode([
                    'app_key' => '',
                    'app_secret' => '',
                    'username' => '',
                    'password' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
            [
                'code' => 'nagad',
                'name' => 'Nagad Payment',
                'credentials' => json_encode([
                    'merchant_id' => '',
                    'public_key' => '',
                    'private_key' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
            [
                'code' => 'upay',
                'name' => 'Upay Payment',
                'credentials' => json_encode([
                    'merchant_id' => '',
                    'merchant_key' => '',
                    'merchant_secret' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
            [
                'code' => 'sslcommerz',
                'name' => 'SSLCommerz Hosted Gateway',
                'credentials' => json_encode([
                    'store_id' => '',
                    'store_password' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
            [
                'code' => 'eps',
                'name' => 'EPS (Easy Payment System)',
                'credentials' => json_encode([
                    'merchant_id' => '',
                    'merchant_key' => '',
                    'client_secret' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
            [
                'code' => 'shurjopay',
                'name' => 'Shurjopay Gateway',
                'credentials' => json_encode([
                    'username' => '',
                    'password' => '',
                    'prefix' => 'NOK'
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
            [
                'code' => 'stripe',
                'name' => 'Stripe (International Card Pay)',
                'credentials' => json_encode([
                    'publishable_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
            [
                'code' => 'paypal',
                'name' => 'PayPal Express Checkout',
                'credentials' => json_encode([
                    'client_id' => '',
                    'client_secret' => '',
                    'app_id' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
            ],
        ];

        foreach ($defaultGateways as $gw) {
            $exists = DB::table('payment_gateways')->where('code', $gw['code'])->exists();
            if (!$exists) {
                DB::table('payment_gateways')->insert(array_merge($gw, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    // --- MANUAL PAYMENT CRUD (With QR Standee Image Support) ---
    public function storeManual(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'account_number'       => 'required|string|max:255',
            'instruction'          => 'nullable|string',
            'qr_code_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'qr_code_image_media'  => 'nullable|string',
        ]);

        $qrImagePath = null;
        if ($request->hasFile('qr_code_image')) {
            $qrImagePath = $this->savePaymentFile($request->file('qr_code_image'), 'payment_qr');
        } elseif ($request->filled('qr_code_image_media')) {
            $qrImagePath = $request->input('qr_code_image_media');
        }

        $data = [
            'name'           => $request->name,
            'account_number' => $request->account_number,
            'instruction'    => $request->instruction,
            'is_active'      => $request->has('is_active') ? 1 : 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('manual_payment_methods', 'qr_code_image')) {
            $data['qr_code_image'] = $qrImagePath;
        }

        DB::table('manual_payment_methods')->insert($data);

        return redirect()->back()->with('success', 'Manual payment method added successfully.');
    }

    public function updateManual(Request $request, $id)
    {
        $method = DB::table('manual_payment_methods')->where('id', $id)->first();
        abort_if(!$method, 404);

        $request->validate([
            'name'                 => 'required|string|max:255',
            'account_number'       => 'required|string|max:255',
            'instruction'          => 'nullable|string',
            'qr_code_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'qr_code_image_media'  => 'nullable|string',
        ]);

        $qrImagePath = $method->qr_code_image ?? null;

        if ($request->input('remove_qr_image') == '1') {
            if ($qrImagePath) {
                $this->deletePaymentFile($qrImagePath);
            }
            $qrImagePath = null;
        }

        if ($request->hasFile('qr_code_image')) {
            if ($qrImagePath) {
                $this->deletePaymentFile($qrImagePath);
            }
            $qrImagePath = $this->savePaymentFile($request->file('qr_code_image'), 'payment_qr');
        } elseif ($request->filled('qr_code_image_media')) {
            $qrImagePath = $request->input('qr_code_image_media');
        }

        $updateData = [
            'name'           => $request->name,
            'account_number' => $request->account_number,
            'instruction'    => $request->instruction,
            'is_active'      => $request->has('is_active') ? 1 : 0,
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('manual_payment_methods', 'qr_code_image')) {
            $updateData['qr_code_image'] = $qrImagePath;
        }

        DB::table('manual_payment_methods')->where('id', $id)->update($updateData);

        return redirect()->back()->with('success', 'Manual payment method updated successfully.');
    }

    public function destroyManual($id)
    {
        $method = DB::table('manual_payment_methods')->where('id', $id)->first();
        if ($method && !empty($method->qr_code_image)) {
            $this->deletePaymentFile($method->qr_code_image);
        }

        DB::table('manual_payment_methods')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Payment method deleted successfully.');
    }

    public function toggleManual($id)
    {
        $method = DB::table('manual_payment_methods')->where('id', $id)->first();
        if ($method) {
            DB::table('manual_payment_methods')->where('id', $id)->update([
                'is_active'  => $method->is_active ? 0 : 1,
                'updated_at' => now(),
            ]);
        }
        return redirect()->back()->with('success', 'Payment status updated.');
    }

    // --- AUTOMATED GATEWAYS UPDATE ---
    public function updateGateway(Request $request, $id)
    {
        $gateway = DB::table('payment_gateways')->where('id', $id)->first();
        abort_if(!$gateway, 404);

        $credentials = $request->input('credentials', []);

        DB::table('payment_gateways')->where('id', $id)->update([
            'credentials' => json_encode($credentials),
            'is_live'     => $request->has('is_live') ? 1 : 0,
            'is_active'   => $request->has('is_active') ? 1 : 0,
            'updated_at'  => now(),
        ]);

        return redirect()->back()->with('success', $gateway->name . ' configuration saved successfully.');
    }

    public function toggleGateway($id)
    {
        $gateway = DB::table('payment_gateways')->where('id', $id)->first();
        if ($gateway) {
            DB::table('payment_gateways')->where('id', $id)->update([
                'is_active'  => $gateway->is_active ? 0 : 1,
                'updated_at' => now(),
            ]);
        }
        return redirect()->back()->with('success', 'Gateway status updated.');
    }
}