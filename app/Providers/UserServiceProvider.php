<?php

namespace App\Providers;

use App\Services\Implementation\UserServiceImpl;
use App\Services\UserService;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function provides()
    {
        return [UserService::class];
    }
    /**
     * Register services.
     */
    public function register(): void
    {
        App::singleton(UserService::class, function () {
            return new UserServiceImpl();
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
