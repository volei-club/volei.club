<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthService;
use App\Services\UserService;

class AuthController extends Controller
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

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => __('api_auth_standard.invalid_credentials')
            ], 401);
        }

        $user = $this->userService->getUserByEmail($request->email);
        if (!$user) {
            return response()->json([
                'message' => __('api_auth_standard.user_not_found')
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->authService->auditLogin($user, 'direct_credentials');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
