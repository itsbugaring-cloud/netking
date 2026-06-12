<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
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
        Paginator::defaultView('vendor.pagination.netking');
        Paginator::defaultSimpleView('vendor.pagination.netking');

        RateLimiter::for('public_payment', function (Request $request) {
            $customerCode = strtoupper(trim((string)$request->input('customer_code', '')));
            return Limit::perMinute(5)->by($customerCode ?: $request->ip());
        });
    }
}

