<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            // Trimitem mailable-ul pe care tocmai l-am creat
            return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Notificare de resetare a parolei')
            ->view('emails.templates.reset_password', [
            'url' => route('password.reset', ['token' => $token]),
            'email' => $notifiable->getEmailForPasswordReset()
            ]);
        });

        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });
    }
}
