<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImpersonationService;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    protected $impersonationService;

    public function __construct(ImpersonationService $impersonationService)
    {
        $this->impersonationService = $impersonationService;
    }

    /**
     * Start impersonating another user.
     */
    public function impersonate(Request $request, $id)
    {
        $admin = $request->user();
        $targetUser = User::findOrFail($id);

        try {
            $targetToken = $this->impersonationService->startImpersonation($admin, $targetUser);

            return response()->json([
                'status' => 'success',
                'message' => 'Te-ai logat ca ' . $targetUser->name,
                'token' => $targetToken->plainTextToken
            ]);
        }
        catch (\Exception $e) {
            $code = $e->getMessage() === 'Cannot self-impersonate.' ? 400 : 403;
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $code);
        }
    }

    /**
     * Leave the impersonated account.
     */
    public function leave(Request $request)
    {
        $this->impersonationService->stopImpersonation($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Sesiunea de impersonare s-a incheiat.'
        ]);
    }
}
