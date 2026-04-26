<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind(
        //     \Backpack\PermissionManager\app\Http\Controllers\UserCrudController::class, //this is package controller
        //     \App\Http\Controllers\Admin\UserCrudController::class //this should be your own controller
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
