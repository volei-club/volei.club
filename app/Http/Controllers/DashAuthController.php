<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class DashAuthController extends Controller
{
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
            // Generăm cod 2FA
            $code = rand(100000, 999999);
            $user->two_factor_code = $code;
            $user->two_factor_expires_at = now()->addMinutes(10);
            $user->save();

            // Store user id in session temporarily for 2FA verification
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->has('remember'));

            // Trimitem email
            Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($code));
            \Illuminate\Support\Facades\Log::info("Codul 2FA pentru {$user->email} este: {$code}");

            return redirect()->route('dash.2fa.show');
        }

        return back()->withErrors([
            'email' => 'Datele de autentificare sunt incorecte.',
        ])->onlyInput('email');
    }

    public function show2fa(Request $request)
    {
        // Protecția se face pe frontend (SPA) verificând sessionStorage['2fa_user_id'].
        // Nu mai verificăm sesiunea de Laravel aici.
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

        if (!$user || $user->two_factor_code !== $request->code || now()->greaterThan($user->two_factor_expires_at)) {
            return back()->withErrors(['code' => 'Codul este invalid sau a expirat.']);
        }

        // Reset 2FA code
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

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

        // Generate new code
        $code = rand(100000, 999999);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        // Send email
        Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($code));
        \Illuminate\Support\Facades\Log::info("Codul 2FA (retrimis) pentru {$user->email} este: {$code}");

        return response()->json(['success' => true, 'message' => 'Codul a fost retrimis.']);
    }

    public function showRecovery()
    {
        return view('dash.auth.recuperare');
    }

    public function showResetForm(Request $request, $token = null)
    {
        // Preluăm toate înregistrările din tabelul password_reset_tokens
        // Laravel salvează tokenul criptat cu Hash::check în baza de date
        // Alternativ, în versiunile noi folosește SHA-256 hash(hashalgs).
        // Cea mai sigură metodă e să verificăm direct prin broker dacă tokenul aparține
        // măcar unui utilizator (folosind doar tokenul, din păcate, broker-ul cere și mailul).

        // În Laravel 11, tokenurile sunt stocate ca text clar pt e-mail + hash ptr token
        $tokenExists = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->get()->filter(function ($record) use ($token) {
            return Hash::check($token, $record->token);
        })->isNotEmpty();

        if (!$tokenExists) {
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
            // Generăm un API Token în loc de Login Sesiune
            $token = $user->createToken('auth_token')->plainTextToken;

            // Returnăm un view minimalist care înscrie token-ul în localStorage
            return view('dash.auth.oauth_proxy', ['token' => $token]);
        }
        else {
            // Contul nu există local
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
