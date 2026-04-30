<?php

namespace App\Providers;

use App\Models\Ncr;
use App\Observers\NcrObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{




    
    /**
     * Register any application services.
     */
    public function register(): void
    {
      Ncr::observe(NcrObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         URL::forceRootUrl('https://erp.htcl.co.in');
    }
}
