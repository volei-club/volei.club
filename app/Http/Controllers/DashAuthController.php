<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AuthService;
use App\Services\UserService;

class DashAuthController extends Controller
{
    protected $authService;
    protected $userService;

    public function __construct(AuthService $authService, UserService $userService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
    }

    public function showLogin(Request $request, $locale)
    {
        return view('dash.auth.login');
    }

    public function login(Request $request, $locale)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->userService->getUserByEmail($request->email);

        if ($user && Hash::check($request->password, $user->password)) {
            $this->authService->generateAndSend2FA($user);

            // Store user id in session temporarily for 2FA verification
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->has('remember'));

            return redirect()->route('dash.2fa.show');
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function show2fa(Request $request, $locale)
    {
        return view('dash.auth.2fa');
    }

    public function verify2fa(Request $request, $locale)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('dash.login');
        }

        $user = $this->userService->getUserById($userId);

        if (!$user || !$this->authService->verify2FA($user, $request->code)) {
            return back()->withErrors(['code' => __('auth.2fa_invalid')]);
        }

        $remember = $request->session()->get('2fa_remember', false);
        Auth::login($user, $remember);
        $request->session()->forget('2fa_user_id');
        $request->session()->forget('2fa_remember');

        return redirect()->route('dash.index');
    }

    public function resend2fa(Request $request, $locale)
    {
        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => __('auth.user_not_found')], 400);
        }

        $user = $this->userService->getUserById($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('auth.user_not_found')], 404);
        }

        $this->authService->generateAndSend2FA($user);

        return response()->json(['success' => true, 'message' => __('auth.2fa_resent')]);
    }

    public function showRecovery(Request $request, $locale)
    {
        return view('dash.auth.recuperare');
    }

    public function showResetForm(Request $request, $locale, $token = null)
    {
        if (!$this->authService->validateResetToken($token)) {
            return redirect()->route('dash.login')
                ->withErrors(['email' => __('auth.password_reset_error')]);
        }

        return view('dash.auth.reset', ['token' => $token]);
    }

    public function redirectToGoogle(Request $request, $locale)
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')
            ->redirectUrl(route('dash.google.callback'))
            ->redirect();
    }

    public function handleGoogleCallback(Request $request, $locale)
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->redirectUrl(route('dash.google.callback'))
                ->user();
        }
        catch (\Exception $e) {
            return redirect()->route('dash.login')->withErrors(['email' => __('auth.google_error')]);
        }

        $user = $this->userService->getUserByEmail($googleUser->getEmail());

        if ($user) {
            $this->authService->auditLogin($user, 'google');

            // Generăm un API Token în loc de Login Sesiune (as per existing logic)
            $token = $user->createToken('auth_token')->plainTextToken;

            // Returnăm un view minimalist care înscrie token-ul în localStorage
            return view('dash.auth.oauth_proxy', ['token' => $token]);
        }
        else {
            return view('dash.auth.google_error', ['email' => $googleUser->getEmail()]);
        }
    }

    public function index(Request $request, $locale)
    {
        return view('dash.index');
    }

    public function logout(Request $request, $locale)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('dash.login');
    }
}
