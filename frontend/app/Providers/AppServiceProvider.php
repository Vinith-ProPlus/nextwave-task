<?php

namespace App\Providers;

use App\Services\ApiService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Share authentication state with all views
        View::composer('*', function ($view) {
            $apiService = app(ApiService::class);
            $view->with('isAuthenticated', $apiService->isAuthenticated());

            if ($apiService->isAuthenticated()) {
                $profile = $apiService->getProfile();
                $view->with('currentUser', $profile['success'] ? $profile['data'] : null);
            }
        });
    }
}
