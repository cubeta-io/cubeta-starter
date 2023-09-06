<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ComponentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path() . '/views/components/form');
        Blade::anonymousComponentPath(resource_path() . '/views/components/form/checkboxes');
        Blade::anonymousComponentPath(resource_path() . '/views/components/form/fields');
        Blade::anonymousComponentPath(resource_path() . '/views/components/form/validation');
        Blade::anonymousComponentPath(resource_path() . '/views/components/show');
        Blade::anonymousComponentPath(resource_path() . '/views/components/images');
    }
}
