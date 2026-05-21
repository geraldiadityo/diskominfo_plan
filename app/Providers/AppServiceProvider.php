<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DashboardRepositoryInterface;
use App\Repositories\Eloquent\DashboardRepository;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
