<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application locale.
     *
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLocale(string $locale)
    {
        if (in_array($locale, ['en', 'ro'])) {
            Session::put('locale', $locale);
            Session::save();
        }

        return redirect()->back();
    }
}
