<?php

namespace App\Providers;

use App\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register PaymentGatewayManager as singleton
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            return new PaymentGatewayManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load API routes
        Route::prefix('api/v1')
            ->middleware('api')
            ->group(base_path('routes/api/v1.php'));
    }
}
