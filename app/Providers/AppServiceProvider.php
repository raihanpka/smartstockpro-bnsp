<?php

namespace App\Providers;

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
        \App\Models\Product::observe(\App\Observers\AuditObserver::class);
        \App\Models\StockTransaction::observe(\App\Observers\AuditObserver::class);
        \App\Models\Warehouse::observe(\App\Observers\AuditObserver::class);
        \App\Models\Category::observe(\App\Observers\AuditObserver::class);
        \App\Models\Supplier::observe(\App\Observers\AuditObserver::class);
    }
}
