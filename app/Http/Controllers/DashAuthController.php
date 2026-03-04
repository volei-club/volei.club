<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\AuthService;

class DashAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLogin()
    {
        return view('dash.auth.login');
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

            // Store user id in session temporarily for 2FA verification
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->has('remember'));

            return redirect()->route('dash.2fa.show');
        }

        return back()->withErrors([
            'email' => 'Datele de autentificare sunt incorecte.',
        ])->onlyInput('email');
    }

    public function show2fa(Request $request)
    {
        return view('dash.auth.2fa');
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('dash.login');
        }

        $user = User::find($userId);

        if (!$user || !$this->authService->verify2FA($user, $request->code)) {
            return back()->withErrors(['code' => 'Codul este invalid sau a expirat.']);
        }

        $remember = $request->session()->get('2fa_remember', false);
        Auth::login($user, $remember);
        $request->session()->forget('2fa_user_id');
        $request->session()->forget('2fa_remember');

        return redirect()->route('dash.index');
    }

    public function resend2fa(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User not found in session'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $this->authService->generateAndSend2FA($user);

        return response()->json(['success' => true, 'message' => 'Codul a fost retrimis.']);
    }

    public function showRecovery()
    {
        return view('dash.auth.recuperare');
    }

    public function showResetForm(Request $request, $token = null)
    {
        if (!$this->authService->validateResetToken($token)) {
            return redirect()->route('dash.login')
                ->withErrors(['email' => 'Link-ul de resetare este invalid sau a fost deja folosit.']);
        }

        return view('dash.auth.reset', ['token' => $token]);
    }

    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        }
        catch (\Exception $e) {
            return redirect()->route('dash.login')->withErrors(['email' => 'Eroare la conectarea cu Google. Trebuie instalat pachetul laravel/socialite sau configurat client id/secret.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

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

    public function index()
    {
        return view('dash.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('dash.login');
    }
}
