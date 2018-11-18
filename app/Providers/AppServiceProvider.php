<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Invoice_detail;
use App\Observers\Invoice_detailObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Invoice_detail::observe(Invoice_detailObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
