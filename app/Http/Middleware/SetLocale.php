<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale && in_array($locale, ['en', 'ro'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            \Illuminate\Support\Facades\URL::defaults(['locale' => $locale]);
        }
        elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
            \Illuminate\Support\Facades\URL::defaults(['locale' => Session::get('locale')]);
        }

        return $next($request);
    }
}
