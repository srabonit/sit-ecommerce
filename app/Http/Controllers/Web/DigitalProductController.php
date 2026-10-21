<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DigitalProductController extends Controller
{
    /**
     * টোকেন যাচাই, পেমেন্ট স্ট্যাটাস, লিমিট ও মেয়াদ সাপেক্ষে নিরাপদ ডাউনলোড
     */
    public function downloadFile(Request $request, $token)
    {
        $downloadRecord = DB::table('order_downloads')
            ->join('product_downloads', 'order_downloads.product_download_id', '=', 'product_downloads.id')
            ->join('orders', 'order_downloads.order_id', '=', 'orders.id')
            ->where('order_downloads.download_token', $token)
            ->select(
                'order_downloads.*',
                'product_downloads.file_name',
                'product_downloads.file_path',
                'product_downloads.file_url',
                'orders.payment_status',
                'orders.user_id'
            )
            ->first();

        if (!$downloadRecord) {
            abort(404, 'Invalid or expired download link.');
        }

        // সিকিউরিটি চেক ১: পেমেন্ট সফল বা পেইড হতে হবে
        if (!in_array(strtolower($downloadRecord->payment_status ?? ''), ['paid', 'completed'])) {
            abort(403, 'Payment is not completed yet. Digital access will be available once payment is verified.');
        }

        // সিকিউরিটি চেক ২: ক্রেতা অথেনটিকেশন চেক
        if (Auth::check() && $downloadRecord->user_id && Auth::id() != $downloadRecord->user_id) {
            abort(403, 'Unauthorized access to this download.');
        }

        // সিকিউরিটি চেক ৩: মেয়াদ যাচাই
        if ($downloadRecord->expires_at && now()->gt($downloadRecord->expires_at)) {
            abort(403, 'This download link has expired.');
        }

        // সিকিউরিটি চেক ৪: ডাউনলোড লিমিট যাচাই
        if (!is_null($downloadRecord->download_limit) && $downloadRecord->download_count >= $downloadRecord->download_limit) {
            abort(403, 'Download limit reached for this file.');
        }

        // ডাউনলোড কাউন্ট বৃদ্ধি
        DB::table('order_downloads')->where('id', $downloadRecord->id)->increment('download_count');

        // যদি এক্সটার্নাল ড্রাইভ বা ক্লাউড URL হয়
        if (!empty($downloadRecord->file_url)) {
            return redirect()->away($downloadRecord->file_url);
        }

        // সিকিউর স্টোরেজ থেকে ডাউনলোড
        $securePath = storage_path('app/' . $downloadRecord->file_path);
        if (!file_exists($securePath)) {
            abort(404, 'File not found on storage server.');
        }

        return response()->download($securePath, $downloadRecord->file_name);
    }

    /**
     * কাস্টমার প্যানেলে কেনা ডিজিটাল প্রোডাক্ট ও ডাউনলোডের তালিকা
     */
    public function userDownloads()
    {
        $userId = Auth::id();
        $downloads = DB::table('order_downloads')
            ->join('orders', 'order_downloads.order_id', '=', 'orders.id')
            ->join('product_downloads', 'order_downloads.product_download_id', '=', 'product_downloads.id')
            ->join('products', 'order_downloads.product_id', '=', 'products.id')
            ->where('orders.user_id', $userId)
            ->whereIn('orders.payment_status', ['paid', 'completed'])
            ->select(
                'order_downloads.*',
                'orders.invoice_no',
                'product_downloads.file_name',
                'products.title as product_title'
            )
            ->latest('order_downloads.id')
            ->paginate(15);

        return view('frontend.user.downloads', compact('downloads'));
    }
}