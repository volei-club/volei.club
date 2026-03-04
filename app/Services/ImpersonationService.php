<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class ImpersonationService
{
    /**
     * Start impersonating a target user.
     * Returns the impersonation token.
     */
    public function startImpersonation(User $admin, User $target)
    {
        if ($admin->role !== 'administrator') {
            throw new \Exception('Unauthorized: Only administrators can impersonate.');
        }

        if ($admin->id === $target->id) {
            throw new \Exception('Cannot self-impersonate.');
        }

        $tokenName = 'ImpersonationTokenByAdmin_' . $admin->id;
        return $target->createToken($tokenName);
    }

    /**
     * Stop the current impersonation session.
     */
    public function stopImpersonation(User $user)
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->currentAccessToken();

        if ($token && isset($token->name) && str_starts_with($token->name, 'ImpersonationTokenByAdmin_')) {
            $token->delete();
            return true;
        }

        return false;
    }
}
