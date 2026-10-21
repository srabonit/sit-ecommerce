<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use App\Services\ActivityLogger;

class AdminProfileController extends Controller
{
    protected function getPublicRoot(): string
    {
        return isset($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT']) 
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') 
            : public_path();
    }

    /**
     * প্রোফাইল ছবি সেভ করার হেল্পার (ফাইল আপলোড)
     */
    protected function saveProfilePhoto($file): string
    {
        $filename = 'admin_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $baseDir = $this->getPublicRoot() . '/uploads/staff/photos';

        if (!file_exists($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $file->move($baseDir, $filename);

        return 'uploads/staff/photos/' . $filename;
    }

    /**
     * পুরাতন ছবি ডিলিট করার হেল্পার
     */
    protected function deleteOldPhoto(?string $path): void
    {
        if (empty($path) || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        $root = $this->getPublicRoot();
        $file1 = $root . '/' . $clean;
        $file2 = $root . '/storage/' . $clean;

        if (file_exists($file1)) @unlink($file1);
        if (file_exists($file2)) @unlink($file2);
    }

    /**
     * ১. প্রোফাইল ও অ্যাক্টিভিটি ড্যাশবোর্ড ভিউ
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // ইউজারের নিজস্ব শাখা (ব্রাঞ্চ) ইনফো
        $branchName = 'Head Office / Online Store';
        if (!empty($user->branch_id) && Schema::hasTable('branches')) {
            $branchName = DB::table('branches')->where('id', $user->branch_id)->value('name') ?: ('Branch #' . $user->branch_id);
        }

        // ২ নং রিকোয়ারমেন্ট: ইউজারের নিজস্ব অ্যাক্টিভিটি লগ রিড
        $myActivities = collect();
        if (Schema::hasTable('activity_logs')) {
            $myActivities = DB::table('activity_logs')
                ->where('user_id', $userId)
                ->latest('id')
                ->take(30)
                ->get();
        }

        // ইউজারের সক্রিয় ডিভাইস সেশন
        $mySessions = collect();
        if (Schema::hasTable('active_user_sessions')) {
            $mySessions = DB::table('active_user_sessions')
                ->where('user_id', $userId)
                ->where('is_revoked', false)
                ->latest('last_activity')
                ->take(10)
                ->get();
        }

        return view('admin.profile.index', compact('user', 'branchName', 'myActivities', 'mySessions'));
    }

    /**
     * ২. প্রোফাইল তথ্য ও ছবি আপডেট (ফাইল আপলোড অথবা ইমেজ URL)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'phone'       => 'required|string|max:20',
            'designation' => 'nullable|string|max:100',
            'bio'         => 'nullable|string|max:500',
            'photo_file'  => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:3072',
            'photo_url'   => 'nullable|url|max:1000',
        ]);

        $updateData = [
            'name'        => trim($request->name),
            'email'       => strtolower(trim($request->email)),
            'phone'       => trim($request->phone),
            'designation' => $request->designation ? trim($request->designation) : $user->designation,
            'bio'         => $request->bio ? trim($request->bio) : null,
            'updated_at'  => now(),
        ];

        // ছবি আপডেট লজিক: ফাইল আপলোড অগ্রাধিকার পাবে, না থাকলে ইমেজ URL
        if ($request->hasFile('photo_file')) {
            $this->deleteOldPhoto($user->photo);
            $updateData['photo'] = $this->saveProfilePhoto($request->file('photo_file'));
        } elseif ($request->filled('photo_url')) {
            $this->deleteOldPhoto($user->photo);
            $updateData['photo'] = trim($request->photo_url);
        }

        DB::table('users')->where('id', $user->id)->update($updateData);

        // অ্যাক্টিভিটি ট্রেইলে লগ সংরক্ষণ
        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('profile_update', 'user_profile', 'Updated personal profile details', [
                'record_id'  => $user->id,
                'record_ref' => $user->email,
                'new_values' => [
                    'name'  => $updateData['name'],
                    'email' => $updateData['email'],
                    'phone' => $updateData['phone'],
                ],
                'result' => 'success'
            ]);
        }

        Cache::flush();

        return back()->with('success', 'আপনার প্রোফাইল তথ্য ও ছবি সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * ৩. পাসওয়ার্ড পরিবর্তন
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($request->new_password),
            'updated_at' => now(),
        ]);

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('password_change', 'security', 'User updated account password', [
                'record_id'  => $user->id,
                'record_ref' => $user->email,
                'result'     => 'success'
            ]);
        }

        return back()->with('success', 'অ্যাকাউন্টের পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
    }
}