<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxPayment;
use App\Services\ActivityLogger;
use App\Services\ImagePathService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TaxPaymentController extends Controller
{
    /**
     * Display a listing of government tax & VAT payments (Treasury Challans).
     */
    public function index(Request $request)
    {
        $query = TaxPayment::query()->with('creator:id,name');

        // Search Filter (Challan, Mushak, Period, Notes)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('challan_number', 'like', "%{$search}%")
                  ->orWhere('mushak_reference', 'like', "%{$search}%")
                  ->orWhere('tax_period', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Tax Period Filter
        if ($request->filled('tax_period')) {
            $query->where('tax_period', trim($request->tax_period));
        }

        // Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Payment Method Filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Date Range Filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('payment_date', [
                Carbon::parse($request->start_date)->toDateString(),
                Carbon::parse($request->end_date)->toDateString()
            ]);
        }

        $taxPayments = $query->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Bank Accounts for Payment Modal
        $bankAccounts = [];
        if (Schema::hasTable('bank_accounts')) {
            $bankAccounts = DB::table('bank_accounts')
                ->where('is_active', true)
                ->orderBy('account_name')
                ->get();
        }

        // Summary Calculations for Current Filtered Scope
        $totalPaidAmount = (float)(clone $query)->where('payment_status', 'completed')->sum('amount');
        $totalPendingAmount = (float)(clone $query)->where('payment_status', 'pending')->sum('amount');

        return view('admin.tax.payments.index', compact(
            'taxPayments',
            'bankAccounts',
            'totalPaidAmount',
            'totalPendingAmount'
        ));
    }

    /**
     * Store a newly created government tax payment / treasury challan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_date'     => 'required|date',
            'tax_period'       => 'required|string|max:50',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string|max:50',
            'bank_account_id'  => 'nullable|integer',
            'challan_number'   => 'nullable|string|max:100',
            'mushak_reference' => 'nullable|string|max:100',
            'document_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'notes'            => 'nullable|string|max:1000',
            'payment_status'   => 'required|in:completed,pending,rejected',
        ], [
            'payment_date.required'   => 'ট্যাক্স জমার তারিখ প্রদান করা আবশ্যক।',
            'tax_period.required'     => 'ট্যাক্স পিরিয়ড (যেমন: October 2026) উল্লেখ করুন।',
            'amount.required'         => 'জমার পরিমাণ উল্লেখ করা আবশ্যক।',
            'payment_method.required' => 'পেমেন্ট মেথড নির্বাচন করুন।',
            'document_file.max'       => 'চালান ফাইলের সাইজ সর্বোচ্চ ৫ মেগাবাইট হতে পারবে।',
        ]);

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $filename = 'challan_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('uploads/tax_challans', $filename, 'public');
            }

            $payment = TaxPayment::create([
                'payment_date'     => Carbon::parse($validated['payment_date'])->toDateString(),
                'tax_period'       => trim($validated['tax_period']),
                'amount'           => (float)$validated['amount'],
                'payment_method'   => $validated['payment_method'],
                'bank_account_id'  => $validated['bank_account_id'] ?? null,
                'challan_number'   => $validated['challan_number'] ?? null,
                'mushak_reference' => $validated['mushak_reference'] ?? null,
                'document_file'    => $filePath,
                'notes'            => $validated['notes'] ?? null,
                'payment_status'   => $validated['payment_status'],
                'created_by'       => auth()->id(),
            ]);

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_payment_created', "Recorded Government Tax Payment of ৳{$payment->amount} for Period: {$payment->tax_period}", [
                    'payment_id' => $payment->id,
                    'amount'     => $payment->amount,
                    'challan'    => $payment->challan_number,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'সরকারি কোষাগারে ট্যাক্স জমার রেকর্ড সফলভাবে সংরক্ষিত হয়েছে।',
                    'data'    => $payment,
                ]);
            }

            return redirect()->route('admin.tax.payments.index')->with('success', 'ট্যাক্স জমার চালান সফলভাবে সংরক্ষিত হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'পেমেন্ট এন্ট্রি ব্যর্থ হয়েছে: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'পেমেন্ট এন্ট্রি ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Get tax payment details for view / edit modal via AJAX.
     */
    public function show(int $id): JsonResponse
    {
        $payment = TaxPayment::with('creator:id,name')->findOrFail($id);

        $documentUrl = null;
        if (!empty($payment->document_file)) {
            if (filter_var($payment->document_file, FILTER_VALIDATE_URL)) {
                $documentUrl = $payment->document_file;
            } elseif (Storage::disk('public')->exists($payment->document_file)) {
                $documentUrl = asset('storage/' . ltrim($payment->document_file, '/'));
            } elseif (file_exists(public_path($payment->document_file))) {
                $documentUrl = asset(ltrim($payment->document_file, '/'));
            } else {
                $documentUrl = asset('uploads/' . ltrim($payment->document_file, '/'));
            }
        }

        return response()->json([
            'success' => true,
            'data'    => array_merge($payment->toArray(), [
                'document_url' => $documentUrl,
            ]),
        ]);
    }

    /**
     * Update an existing government tax payment record.
     */
    public function update(Request $request, int $id)
    {
        $payment = TaxPayment::findOrFail($id);

        $validated = $request->validate([
            'payment_date'     => 'required|date',
            'tax_period'       => 'required|string|max:50',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string|max:50',
            'bank_account_id'  => 'nullable|integer',
            'challan_number'   => 'nullable|string|max:100',
            'mushak_reference' => 'nullable|string|max:100',
            'document_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'notes'            => 'nullable|string|max:1000',
            'payment_status'   => 'required|in:completed,pending,rejected',
        ]);

        try {
            DB::beginTransaction();

            $filePath = $payment->document_file;
            if ($request->hasFile('document_file')) {
                // Remove old document if present
                if (!empty($filePath) && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $file = $request->file('document_file');
                $filename = 'challan_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('uploads/tax_challans', $filename, 'public');
            }

            $payment->update([
                'payment_date'     => Carbon::parse($validated['payment_date'])->toDateString(),
                'tax_period'       => trim($validated['tax_period']),
                'amount'           => (float)$validated['amount'],
                'payment_method'   => $validated['payment_method'],
                'bank_account_id'  => $validated['bank_account_id'] ?? null,
                'challan_number'   => $validated['challan_number'] ?? null,
                'mushak_reference' => $validated['mushak_reference'] ?? null,
                'document_file'    => $filePath,
                'notes'            => $validated['notes'] ?? null,
                'payment_status'   => $validated['payment_status'],
            ]);

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_payment_updated', "Updated Tax Payment record #{$payment->id} for Period: {$payment->tax_period}", [
                    'payment_id' => $payment->id,
                    'amount'     => $payment->amount,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ট্যাক্স পেমেন্ট তথ্য সফলভাবে আপডেট হয়েছে।',
                    'data'    => $payment,
                ]);
            }

            return redirect()->route('admin.tax.payments.index')->with('success', 'ট্যাক্স পেমেন্ট তথ্য সফলভাবে আপডেট হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'আপডেট ব্যর্থ হয়েছে: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'আপডেট ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Download or view the uploaded treasury challan file safely.
     */
    public function downloadChallan(int $id)
    {
        $payment = TaxPayment::findOrFail($id);

        if (empty($payment->document_file)) {
            return back()->with('error', 'এই রেকর্ডের সাথে কোনো চালান ফাইল যুক্ত নেই।');
        }

        $fileName = 'Treasury_Challan_' . ($payment->challan_number ?: $payment->id) . '.' . pathinfo($payment->document_file, PATHINFO_EXTENSION);

        if (Storage::disk('public')->exists($payment->document_file)) {
            return Storage::disk('public')->download($payment->document_file, $fileName);
        }

        $fullPath = public_path($payment->document_file);
        if (file_exists($fullPath)) {
            return response()->download($fullPath, $fileName);
        }

        $storagePath = public_path('storage/' . ltrim($payment->document_file, '/'));
        if (file_exists($storagePath)) {
            return response()->download($storagePath, $fileName);
        }

        return back()->with('error', 'চালান ফাইলটি সার্ভারে পাওয়া যায়নি।');
    }

    /**
     * Remove the tax payment record safely.
     */
    public function destroy(int $id)
    {
        $payment = TaxPayment::findOrFail($id);

        try {
            DB::beginTransaction();

            $period = $payment->tax_period;
            $amount = $payment->amount;

            if (!empty($payment->document_file) && Storage::disk('public')->exists($payment->document_file)) {
                Storage::disk('public')->delete($payment->document_file);
            }

            $payment->delete();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_payment_deleted', "Deleted Tax Payment of ৳{$amount} for Period: {$period}");
            }

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ট্যাক্স পেমেন্ট রেকর্ডটি সফলভাবে মুছে ফেলা হয়েছে।',
                ]);
            }

            return redirect()->route('admin.tax.payments.index')->with('success', 'ট্যাক্স পেমেন্ট রেকর্ডটি মুছে ফেলা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'ডিলিটেশন ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }
}