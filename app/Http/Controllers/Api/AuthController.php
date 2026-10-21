<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Public Customer Self-Registration
     */
    public function customerRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'user_type' => 'customer',
            'is_active' => true,
        ]);

        // Auto-assign Spatie Customer Role
        $user->assignRole('customer');

        // Issue Sanctum Token
        $token = $user->createToken('auth_token_' . $user->id, ['customer'])->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Customer account registered successfully.',
            'data'    => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ]
            ]
        ], 201);
    }

    /**
     * Register New Administrative / Branch Staff User (Protected)
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // Assign Multi-Roles if passed
        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Staff user registered successfully.',
            'data'    => $user->load('roles')
        ], 201);
    }

    /**
     * Universal Login (Customer, Branch Staff & Admin)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email or password credentials.'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Your account is deactivated. Contact system administrator.'
            ], 403);
        }

        // Get permissions array for Sanctum abilities
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // Issue token with user permissions
        $token = $user->createToken('auth_token_' . $user->id, $permissions)->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'data'    => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'user_type'    => $user->user_type,
                    'branch_id'    => $user->branch_id,
                    'warehouse_id' => $user->warehouse_id,
                    'roles'        => $user->getRoleNames(),
                    'permissions'  => $permissions,
                ]
            ]
        ], 200);
    }

    /**
     * Get Authenticated User Profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['branch', 'warehouse', 'roles']);

        return response()->json([
            'status' => true,
            'data'   => [
                'user'        => $user,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ], 200);
    }

    /**
     * Logout and Revoke Current Token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Token revoked successfully. Logged out.'
        ], 200);
    }
}