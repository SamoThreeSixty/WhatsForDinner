<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by('register-ip:' . $request->ip()),
                Limit::perMinute(3)->by('register-email:' . $email . '|' . $request->ip()),
            ];
        });

        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(10)->by('login-ip:' . $request->ip()),
                Limit::perMinute(5)->by('login-email:' . $email . '|' . $request->ip()),
            ];
        });

        RateLimiter::for('forgot-password', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(6)->by('forgot-ip:' . $request->ip()),
                Limit::perMinutes(10, 3)->by('forgot-email:' . $email . '|' . $request->ip()),
            ];
        });

        RateLimiter::for('verification-resend', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(6)->by('verify-resend-ip:' . $request->ip()),
                Limit::perMinutes(10, 3)->by('verify-resend-email:' . $email . '|' . $request->ip()),
            ];
        });

        RateLimiter::for('verification-link', function (Request $request) {
            return Limit::perMinute(6)->by('verify-link:' . $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
