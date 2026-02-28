<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class MailchimpTransportProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Mail::extend('mailchimp', function (array $config = []) {
            $secret = config('services.mailchimp.secret');
            return \Symfony\Component\Mailer\Transport::fromDsn("mandrill+api://{$secret}@default");
        });
    }
}
