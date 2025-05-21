<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EnvServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register a dummy 'env' class to prevent "Target class [env] does not exist" error
        $this->app->singleton('env', function () {
            return new class {
                public function __toString() {
                    return '';
                }
            };
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
