<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating another user.
     */
    public function impersonate(Request $request, $id)
    {
        $admin = $request->user();

        // Strict: only true administrators can do this
        if ($admin->role !== 'administrator') {
            return response()->json(['status' => 'error', 'message' => 'Eroare: Doar administratorii pot impersona.'], 403);
        }

        $targetUser = User::findOrFail($id);

        // Prevent self-impersonation to avoid endless loops or confusion
        if ($admin->id === $targetUser->id) {
            return response()->json(['status' => 'error', 'message' => 'Nu vă puteți impersona propriul cont.'], 400);
        }

        // We create a new token for the target user. The name identifies it as an impersonation session.
        $tokenName = 'ImpersonationTokenByAdmin_' . $admin->id;
        $targetToken = $targetUser->createToken($tokenName);

        // Optional: Keep the original token intact. The frontend simply holds it in localStorage.
        return response()->json([
            'status' => 'success',
            'message' => 'Te-ai logat ca ' . $targetUser->name,
            'token' => $targetToken->plainTextToken
        ]);
    }

    /**
     * Leave the impersonated account.
     */
    public function leave(Request $request)
    {
        // Stergem tokenul curent de impersonare, protejand bd
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();

        if (str_starts_with($token->name, 'ImpersonationTokenByAdmin_')) {
            $token->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sesiunea de impersonare s-a incheiat.'
        ]);
    }
}
