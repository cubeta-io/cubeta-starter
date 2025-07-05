<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\BaseService;
use App\Services\Contracts\IBaseService;

class CubetaStarterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(base_path('resources/views/components/form'));
        Blade::anonymousComponentPath(base_path('resources/views/components/form/checkboxes'));
        Blade::anonymousComponentPath(base_path('resources/views/components/form/fields'));
        Blade::anonymousComponentPath(base_path('resources/views/components/form/validation'));
        Blade::anonymousComponentPath(base_path('resources/views/components/show/'));
        Blade::anonymousComponentPath(base_path('resources/views/components/images'));
    }
}
