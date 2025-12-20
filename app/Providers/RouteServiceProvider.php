<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider {

    public function boot(): void
    {
        $this->configureRateLimiting();
    }
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by(
                $email . '|' . $request->ip()
            );
        });
    }
}


