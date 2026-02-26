<?php

namespace App\Providers;

use App\Support\Tenancy\CurrentHousehold;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CurrentHousehold::class, fn () => new CurrentHousehold());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists(SendgridTransportFactory::class) && class_exists(HttpClient::class)) {
            Mail::extend('sendgrid', function (array $config = []) {
                $factory = new SendgridTransportFactory(
                    null,
                    HttpClient::create($config['client'] ?? [])
                );

                $apiKey = $config['key']
                    ?? config('services.sendgrid.key')
                    ?? config('services.sendgrid.api_key');

                return $factory->create(new Dsn('sendgrid+api', 'default', $apiKey));
            });
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
