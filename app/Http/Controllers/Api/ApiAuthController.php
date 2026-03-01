<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Generate 2FA code
            $code = rand(100000, 999999);
            $user->two_factor_code = $code;
            $user->two_factor_expires_at = now()->addMinutes(10);
            $user->save();

            // Send 2FA email
            Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($code));
            \Illuminate\Support\Facades\Log::info("API Codul 2FA pentru {$user->email} este: {$code}");

            // We do not issue Bearer token yet. We return success so the frontend moves to 2FA screen.
            // Return user id so frontend can pass it to the verification step.
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

        if (!$user || $user->two_factor_code !== $request->code || now()->greaterThan($user->two_factor_expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Codul este invalid sau a expirat.'
            ], 401);
        }

        // Reset 2FA code
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        // Issue Sanctum Token
        // 'auth_token' is the name of the token. We can name it based on device type in the future.
        $token = $user->createToken('auth_token')->plainTextToken;

        // Înregistrăm evenimentul de login în Audit
        \App\Models\AuditLog::create([
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'user_id' => $user->id,
            'event' => 'logged_in',
            'old_values' => null,
            'new_values' => ['method' => 'credentials_2fa'],
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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

        // Generate new code
        $code = rand(100000, 999999);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        // Send email
        Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($code));
        \Illuminate\Support\Facades\Log::info("API Codul 2FA (retrimis) pentru {$user->email} este: {$code}");

        return response()->json([
            'status' => 'success',
            'message' => 'Codul a fost retrimis.'
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.'
        ]);
    }

    public function sendRecovery(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

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

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
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
