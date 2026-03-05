<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\AuthService;
use App\Services\UserService;

class ApiAuthController extends Controller
{
    protected $authService;
    protected $userService;

    public function __construct(AuthService $authService, UserService $userService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->userService->getUserByEmail($request->email);

        if ($user && Hash::check($request->password, $user->password)) {
            $this->authService->generateAndSend2FA($user);

            return response()->json([
                'status' => 'success',
                'message' => __('auth.2fa_sent'),
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('auth.failed')
        ], 401);
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|numeric',
        ]);

        $user = $this->userService->getUserById($request->user_id);

        if (!$user || !$this->authService->verify2FA($user, $request->code)) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth.2fa_invalid')
            ], 401);
        }

        // Issue Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Audit the login
        $this->authService->auditLogin($user, 'credentials_2fa');

        return response()->json([
            'status' => 'success',
            'message' => __('auth.2fa_verified'),
            'token' => $token,
            'user' => $user
        ]);
    }

    public function resend2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $this->userService->getUserById($request->user_id);
        $this->authService->generateAndSend2FA($user);

        return response()->json([
            'status' => 'success',
            'message' => __('auth.2fa_resent')
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('auth.logout')
        ]);
    }

    public function sendRecovery(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->authService->sendResetLink($request->email);

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => __('auth.recovery_sent')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('auth.user_not_found')
        ], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = $this->authService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token')
        );

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'message' => __('auth.password_reset')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('auth.password_reset_error')
        ], 400);
    }
}
