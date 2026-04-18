<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('assistant-chat', function (Request $request): array {
            $key = $request->user()?->id
                ? 'user:'.$request->user()->id
                : 'ip:'.$request->ip();

            return [
                Limit::perMinute(10)->by($key),
                Limit::perDay(150)->by($key),
            ];
        });
    }
}
