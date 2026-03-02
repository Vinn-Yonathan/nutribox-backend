<?php

namespace App\Providers;

use App\Services\Implementation\MenuServiceImpl;
use App\Services\MenuService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function provides()
    {
        return [MenuService::class];
    }
    /**
     * Register services.
     */
    public function register(): void
    {
        App::singleton(MenuService::class, function () {
            return new MenuServiceImpl();
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
