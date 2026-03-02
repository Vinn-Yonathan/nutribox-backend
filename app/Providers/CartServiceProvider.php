<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\Implementation\CartServiceImpl;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function provides()
    {
        return [CartService::class];
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        App::singleton(CartService::class, function () {
            return new CartServiceImpl();
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
