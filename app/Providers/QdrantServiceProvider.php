<?php

namespace App\Providers;

use App\Services\QdrantService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class QdrantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        App::bind('paint',function() {
            return new QdrantService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
