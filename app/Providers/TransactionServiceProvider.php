<?php

namespace App\Providers;

use App\Services\Implementation\TransactionServiceImpl;
use App\Services\TransactionService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider
{
    public function provides()
    {
        return [TransactionService::class];
    }
    /**
     * Register services.
     */
    public function register(): void
    {
        App::singleton(TransactionService::class, function () {
            return new TransactionServiceImpl();
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
