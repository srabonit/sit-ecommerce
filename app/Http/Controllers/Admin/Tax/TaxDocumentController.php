<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxComplianceDocument;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaxDocumentController extends Controller
{
    /**
     * Display a listing of regulatory tax compliance documents.
     */
    public function index(Request $request)
    {
        $query = TaxComplianceDocument::query()->with('uploader:id,name');

        // Search Filter (Title, Reference No, Entity info)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        // Document Type Filter
        if ($request->filled('doc_type')) {
            $query->where('doc_type', $request->doc_type);
        }

        // Expiry Status Filter (Valid vs Expired)
        if ($request->filled('status')) {
            $today = Carbon::today();
            if ($request->status === 'expired') {
                $query->whereNotNull('expiry_date')->where('expiry_date', '<', $today);
            } elseif ($request->status === 'valid') {
                $query->where(function ($q) use ($today) {
                    $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
                });
            } elseif ($request->status === 'expiring_soon') {
                // Expiring in next 30 days
                $query->whereBetween('expiry_date', [$today, $today->copy()->addDays(30)]);
            }
        }

        $documents = $query->latest('id')->paginate(15)->withQueryString();

        // Categorized Document Types
        $docTypes = [
            'bin_certificate'          => 'BIN Certificate',
            'trade_license'            => 'Trade License',
            'procurement_vat_evidence' => 'VAT-Paid Procurement Evidence (মূসক ৬.৩)',
            'mushak_filing'            => 'Mushak Tax Return Filing',
            'treasury_challan'         => 'Treasury Challan Record',
            'vendor_tax_id'            => 'Vendor / Supplier Tax Document',
            'exemption_certificate'    => 'Government Exemption Certificate',
            'other_compliance'         => 'Other Regulatory Compliance',
        ];

        return view('admin.tax.documents.index', compact('documents', 'docTypes'));
    }

    /**
     * Store a newly uploaded compliance document in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doc_type'     => 'required|string|max:50',
            'title'        => 'required|string|max:150',
            'reference_no' => 'nullable|string|max:100',
            'file_path'    => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240', // Max 10MB
            'issue_date'   => 'nullable|date',
            'expiry_date'  => 'nullable|date|after_or_equal:issue_date',
            'vendor_name'  => 'nullable|string|max:150',
            'vendor_bin'   => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:1000',
        ], [
            'doc_type.required'  => 'ডকুমেন্টের ধরণ নির্বাচন করুন।',
            'title.required'     => 'ডকুমেন্টের শিরোনাম দেওয়া আবশ্যক।',
            'file_path.required' => 'ডকুমেন্ট ফাইল আপলোড করা আবশ্যক।',
            'file_path.max'      => 'ফাইলের সর্বোচ্চ সাইজ ১০ মেগাবাইট হতে পারবে।',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file_path');
            $filename = 'tax_doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs('uploads/tax_compliance', $filename, 'public');

            $metaData = [
                'vendor_name'   => $validated['vendor_name'] ?? null,
                'vendor_bin'    => $validated['vendor_bin'] ?? null,
                'notes'         => $validated['notes'] ?? null,
                'original_name' => $file->getClientOriginalName(),
                'file_size_kb'  => round($file->getSize() / 1024, 2),
            ];

            $doc = TaxComplianceDocument::create([
                'doc_type'     => $validated['doc_type'],
                'title'        => $validated['title'],
                'reference_no' => $validated['reference_no'] ?? null,
                'file_path'    => $storedPath,
                'issue_date'   => $validated['issue_date'] ?? null,
                'expiry_date'  => $validated['expiry_date'] ?? null,
                'meta_data'    => $metaData,
                'uploaded_by'  => auth()->id(),
            ]);

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_document_uploaded', "Uploaded Compliance Document: {$doc->title} ({$doc->doc_type})", [
                    'doc_id'       => $doc->id,
                    'reference_no' => $doc->reference_no,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'কমপ্লায়েন্স ডকুমেন্ট সফলভাবে সংরক্ষিত হয়েছে।',
                    'data'    => $doc,
                ]);
            }

            return redirect()->route('admin.tax.documents.index')->with('success', 'কমপ্লায়েন্স ডকুমেন্ট সফলভাবে সংরক্ষিত হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'আপলোড ব্যর্থ হয়েছে: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'আপলোড ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Get document details for view / edit modal via AJAX.
     */
    public function show(int $id): JsonResponse
    {
        $doc = TaxComplianceDocument::with('uploader:id,name')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => array_merge($doc->toArray(), [
                'file_url'   => $doc->file_url,
                'is_expired' => $doc->is_expired,
            ]),
        ]);
    }

    /**
     * Update compliance document metadata.
     */
    public function update(Request $request, int $id)
    {
        $doc = TaxComplianceDocument::findOrFail($id);

        $validated = $request->validate([
            'doc_type'     => 'required|string|max:50',
            'title'        => 'required|string|max:150',
            'reference_no' => 'nullable|string|max:100',
            'file_path'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'issue_date'   => 'nullable|date',
            'expiry_date'  => 'nullable|date|after_or_equal:issue_date',
            'vendor_name'  => 'nullable|string|max:150',
            'vendor_bin'   => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $storedPath = $doc->file_path;
            if ($request->hasFile('file_path')) {
                if (!empty($storedPath) && Storage::disk('public')->exists($storedPath)) {
                    Storage::disk('public')->delete($storedPath);
                }

                $file = $request->file('file_path');
                $filename = 'tax_doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $storedPath = $file->storeAs('uploads/tax_compliance', $filename, 'public');
            }

            $currentMeta = $doc->meta_data ?? [];
            $metaData = array_merge($currentMeta, [
                'vendor_name' => $validated['vendor_name'] ?? null,
                'vendor_bin'  => $validated['vendor_bin'] ?? null,
                'notes'       => $validated['notes'] ?? null,
            ]);

            $doc->update([
                'doc_type'     => $validated['doc_type'],
                'title'        => $validated['title'],
                'reference_no' => $validated['reference_no'] ?? null,
                'file_path'    => $storedPath,
                'issue_date'   => $validated['issue_date'] ?? null,
                'expiry_date'  => $validated['expiry_date'] ?? null,
                'meta_data'    => $metaData,
            ]);

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_document_updated', "Updated Compliance Document #{$doc->id}: {$doc->title}");
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ডকুমেন্টের তথ্য সফলভাবে আপডেট করা হয়েছে।',
                    'data'    => $doc,
                ]);
            }

            return redirect()->route('admin.tax.documents.index')->with('success', 'ডকুমেন্টের তথ্য সফলভাবে আপডেট করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'আপডেট ব্যর্থ হয়েছে: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'আপডেট ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Securely download compliance document file.
     */
    public function download(int $id)
    {
        $doc = TaxComplianceDocument::findOrFail($id);

        if (empty($doc->file_path)) {
            return back()->with('error', 'কোনো ফাইল সংযুক্ত নেই।');
        }

        if (Storage::disk('public')->exists($doc->file_path)) {
            $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $doc->title) . '.' . $ext;
            return Storage::disk('public')->download($doc->file_path, $cleanName);
        }

        $fullPath = public_path($doc->file_path);
        if (file_exists($fullPath)) {
            return response()->download($fullPath);
        }

        return back()->with('error', 'ফাইলটি সার্ভারে পাওয়া যায়নি।');
    }

    /**
     * Delete the compliance document safely.
     */
    public function destroy(int $id)
    {
        $doc = TaxComplianceDocument::findOrFail($id);

        try {
            DB::beginTransaction();

            $title = $doc->title;

            if (!empty($doc->file_path) && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $doc->delete();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_document_deleted', "Deleted Compliance Document: {$title}");
            }

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'কমপ্লায়েন্স ডকুমেন্ট সফলভাবে মুছে ফেলা হয়েছে।',
                ]);
            }

            return redirect()->route('admin.tax.documents.index')->with('success', 'কমপ্লায়েন্স ডকুমেন্ট মুছে ফেলা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'ডিলিটেশন ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }
}