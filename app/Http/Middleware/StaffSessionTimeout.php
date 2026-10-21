<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'staff', 'branch_manager', 'branch_staff'])) {
            $user = Auth::user();

            if ($user->last_activity_at) {
                // ৬ ঘণ্টার বেশি ইনঅ্যাক্টিভ থাকলে লগআউট করে ওটিপি পেইজে রিডাইরেক্ট
                $inactiveHours = Carbon::now()->diffInHours(Carbon::parse($user->last_activity_at));
                if ($inactiveHours >= 6) {
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();

                    return redirect()->route('admin.login')->with('error', 'Session timed out due to 6+ hours of inactivity. Please re-authenticate via OTP.');
                }
            }

            // সেশন টাইম আপডেট
            $user->update(['last_activity_at' => Carbon::now()]);
        }

        return $next($request);
    }
}