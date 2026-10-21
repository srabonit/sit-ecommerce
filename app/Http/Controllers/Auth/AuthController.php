<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * User internal staff, manager ya admin hai ya nahi check karne ka helper
     */
    protected function isStaffOrAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Super Admin check
        if (!empty($user->is_super_admin) || strtolower(trim((string)$user->role)) === 'super_admin') {
            return true;
        }

        // 2. Space aur Underscore dono formats ko normalize karna
        $role = str_replace(' ', '_', strtolower(trim((string)($user->role ?? ''))));

        $staffRoles = [
            'admin',
            'super_admin',
            'branch_manager',
            'staff',
            'manager',
            'cashier',
            'pos_operator',
            'operator',
            'moderator',
            'order_manager',
            'courier_manager',
            'inventory_manager',
            'accounts'
        ];

        if (in_array($role, $staffRoles)) {
            return true;
        }

        // 3. user_type check
        $userType = str_replace(' ', '_', strtolower(trim((string)($user->user_type ?? ''))));
        if (in_array($userType, ['admin', 'staff'])) {
            return true;
        }

        // 4. role_route_permissions table me dynamic role check
        if (Schema::hasTable('role_route_permissions')) {
            $hasRole = DB::table('role_route_permissions')
                ->where('role_name', $user->role)
                ->orWhere('role_name', $role)
                ->exists();

            if ($hasRole) {
                return true;
            }
        }

        return false;
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->isStaffOrAdmin(Auth::user())
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $loginInput = trim($request->input('login'));
        $loginField = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // 30 din se zyada purane trashed accounts ko permanently delete karna
        User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->forceDelete();

        // 30 din ke andar deleted user ka account reactivate karna
        $trashedUser = User::onlyTrashed()->where($loginField, $loginInput)->first();

        if ($trashedUser && Hash::check($request->input('password'), $trashedUser->password)) {
            $trashedUser->restore();
            $trashedUser->update(['is_active' => 1]);

            Auth::login($trashedUser, $request->boolean('remember'));
            $request->session()->regenerate();
            session()->forget('url.intended');

            if ($this->isStaffOrAdmin($trashedUser)) {
                return redirect()->route('admin.dashboard')->with('success', 'Welcome back! Your staff account has been reactivated.');
            }

            return redirect()->route('user.dashboard')->with('success', 'Welcome back! Your deleted account has been reactivated.');
        }

        $credentials = [
            $loginField => $loginInput,
            'password'  => $request->input('password'),
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Account status validation
            if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Aapka account temporarily locked ya deactivated hai.',
                ])->onlyInput('login');
            }

            // Staff/Admin ke liye purana customer intended URL clear karke seedha Admin Dashboard bhejna
            if ($this->isStaffOrAdmin($user)) {
                session()->forget('url.intended');
                return redirect()->route('admin.dashboard');
            }

            // Normal customer ke liye user dashboard
            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors([
            'login' => 'These credentials do not match our records.',
        ])->onlyInput('login');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->isStaffOrAdmin(Auth::user())
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->forceDelete();

        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'email'    => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $cleanPhone = trim($request->phone);

        $user = new User();
        $user->name = trim($request->name);
        $user->phone = $cleanPhone;
        $user->email = $request->filled('email') ? strtolower(trim($request->email)) : null;
        $user->password = Hash::make($request->password);
        $user->role = 'customer';
        $user->user_type = 'customer';
        $user->is_active = 1;
        $user->is_locked = 0;
        $user->is_super_admin = 0;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'Account created successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}