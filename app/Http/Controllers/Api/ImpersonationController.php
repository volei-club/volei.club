<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImpersonationService;
use App\Services\UserService;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    protected $impersonationService;
    protected $userService;

    public function __construct(ImpersonationService $impersonationService, UserService $userService)
    {
        $this->impersonationService = $impersonationService;
        $this->userService = $userService;
    }

    /**
     * Start impersonating another user.
     */
    public function impersonate(Request $request, $id)
    {
        $admin = $request->user();
        $targetUser = $this->userService->getUserById($id);

        try {
            $targetToken = $this->impersonationService->startImpersonation($admin, $targetUser);

            return response()->json([
                'status' => 'success',
                'message' => __('api_impersonation.impersonate_success') . $targetUser->name,
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
            'message' => __('api_impersonation.leave_success')
        ]);
    }
}
