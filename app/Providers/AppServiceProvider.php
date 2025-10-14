<?php

namespace App\Providers;

use App\Services\UserActivityService;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('app_version', function () {
            return '1.0.0-beta.1';
        });

        $this->app->singleton('user_agent', function () {
            $version = app('app_version');
            $url = config('app.url');

            return "Loops/{$version} (Laravel/".app()->version()."; +{$url})";
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('autocomplete', function (Request $request) {
            return [
                Limit::perMinute(10)->by('minute:'.$request->user()->id),
                Limit::perDay(200)->by('day:'.$request->user()->id),
            ];
        });

        Event::listen(Login::class, function ($e) {
            app(UserActivityService::class)->markActive($e->user);
        });
    }
}
