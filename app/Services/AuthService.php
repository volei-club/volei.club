<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Mail\TwoFactorCodeMail;

class AuthService
{
    /**
     * Generate and send 2FA code to user.
     */
    public function generateAndSend2FA(User $user)
    {
        $code = rand(100000, 999999);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new TwoFactorCodeMail($code));
        \Illuminate\Support\Facades\Log::info("Codul 2FA pentru {$user->email} este: {$code}");

        return $code;
    }

    /**
     * Verify 2FA code for a user.
     */
    public function verify2FA(User $user, string $code)
    {
        if ($user->two_factor_code !== $code || now()->greaterThan($user->two_factor_expires_at)) {
            return false;
        }

        // Reset 2FA code upon success
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        return true;
    }

    /**
     * Log a login event in Audit Logs.
     */
    public function auditLogin(User $user, string $method)
    {
        AuditLog::create([
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'user_id' => $user->id,
            'event' => 'logged_in',
            'old_values' => null,
            'new_values' => ['method' => $method],
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle password reset link sending.
     */
    public function sendResetLink(string $email)
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(array $credentials)
    {
        return Password::reset(
            $credentials,
            function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
        );
    }

    /**
     * Check if a password reset token exists (custom check for Dash UI).
     */
    public function validateResetToken(string $token)
    {
        return \Illuminate\Support\Facades\DB::table('password_reset_tokens')->get()->filter(function ($record) use ($token) {
            return Hash::check($token, $record->token);
        })->isNotEmpty();
    }
}
