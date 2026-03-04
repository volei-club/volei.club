<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\AuthService;

class ApiAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $this->authService->generateAndSend2FA($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Credentials verified, 2FA code sent.',
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Datele de autentificare sunt incorecte.'
        ], 401);
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|numeric',
        ]);

        $user = User::find($request->user_id);

        if (!$user || !$this->authService->verify2FA($user, $request->code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Codul este invalid sau a expirat.'
            ], 401);
        }

        // Issue Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Audit the login
        $this->authService->auditLogin($user, 'credentials_2fa');

        return response()->json([
            'status' => 'success',
            'message' => '2FA verified successfully.',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function resend2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);
        $this->authService->generateAndSend2FA($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Codul a fost retrimis.'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.'
        ]);
    }

    public function sendRecovery(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->authService->sendResetLink($request->email);

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ți-am trimis prin e-mail linkul pentru resetarea parolei!'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Nu am putut găsi un utilizator cu această adresă de e-mail.'
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
                'message' => 'Parola a fost resetată cu succes! Te poți autentifica acum.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Eroare la resetarea parolei. Legătura este invalidă sau a expirat.'
        ], 400);
    }
}
