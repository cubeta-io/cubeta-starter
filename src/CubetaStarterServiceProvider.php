<?php

namespace Cubeta\CubetaStarter;

use Cubeta\CubetaStarter\Commands\CreatePivotTable;
use Cubeta\CubetaStarter\Commands\InitialProject;
use Cubeta\CubetaStarter\Commands\InstallWebPackages;
use Cubeta\CubetaStarter\Commands\MakeController;
use Cubeta\CubetaStarter\Commands\MakeFactory;
use Cubeta\CubetaStarter\Commands\MakeMigration;
use Cubeta\CubetaStarter\Commands\MakeModel;
use Cubeta\CubetaStarter\Commands\MakePolicy;
use Cubeta\CubetaStarter\Commands\MakePostmanCollection;
use Cubeta\CubetaStarter\Commands\MakeRepository;
use Cubeta\CubetaStarter\Commands\MakeRequest;
use Cubeta\CubetaStarter\Commands\MakeResource;
use Cubeta\CubetaStarter\Commands\MakeSeeder;
use Cubeta\CubetaStarter\Commands\MakeService;
use Cubeta\CubetaStarter\Commands\MakeTest;
use Cubeta\CubetaStarter\Commands\MakeWebController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CubetaStarterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('cubeta-starter')
            ->hasConfigFile()
            ->hasCommand(CreatePivotTable::class)
            ->hasCommand(MakeController::class)
            ->hasCommand(MakeResource::class)
            ->hasCommand(MakeModel::class)
            ->hasCommand(MakeMigration::class)
            ->hasCommand(MakeFactory::class)
            ->hasCommand(MakeSeeder::class)
            ->hasCommand(MakeRequest::class)
            ->hasCommand(MakeRepository::class)
            ->hasCommand(MakeService::class)
            ->hasCommand(MakeTest::class)
            ->hasCommand(MakePostmanCollection::class)
            ->hasCommand(InitialProject::class)
            ->hasCommand(MakePolicy::class)
            ->hasCommand(MakeWebController::class)
            ->hasCommand(InstallWebPackages::class);
    }

    /**
     * @throws InvalidPackage
     */
    public function register()
    {
        $this->registeringPackage();

        $this->package = new Package();

        $this->package->setBasePath($this->getPackageBaseDir());

        $this->configurePackage($this->package);

        if (empty($this->package->name)) {
            throw InvalidPackage::nameIsRequired();
        }

        foreach ($this->package->configFileNames as $configFileName) {
            $this->mergeConfigFrom($this->package->basePath("/../config/$configFileName.php"), $configFileName);
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/cubeta-starter.php', 'cubeta-starter');

        $this->packageRegistered();

        return $this;
    }

    public function boot()
    {
        parent::boot();

        // publishes
        $this->publishConfigFiles();
        $this->publishExceptionHandler();
        $this->publishAssets();

        // loaded from the package
        $this->loadComponents();
        $this->loadGuiViews();
        $this->loadGuiViewsVariables();

        // register the route files
        $this->registerGuiRoutesFile();

        // register the set locale route
        $this->registerSetLocaleRoute();
    }

    /**
     * publish the package config file
     * @return void
     */
    private function publishConfigFiles(): void
    {
        $this->publishes([__DIR__ . '/../config/cubeta-starter.php' => base_path('config') . '/cubeta-starter.php'], 'cubeta-starter-config');
    }

    /**
     * load the package gui views
     * @return void
     */
    private function loadGuiViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'CubetaStarter');
    }

    /**
     * register route file of the package gui
     * @return void
     */
    private function registerGuiRoutesFile(): void
    {
        if (app()->environment('local')) {
            $this->loadRoutesFrom(__DIR__ . '/Routes/ui-routes.php');
        }
    }

    /**
     * publish the package exception handler
     * @return void
     */
    private function publishExceptionHandler(): void
    {
        $this->publishes([__DIR__ . '/app/Exceptions/handler.php' => base_path('/app/Exceptions/Handler.php')], 'cubeta-starter-handler');
    }

    /**
     * load the variables used in the GUI views
     * @return void
     */
    private function loadGuiViewsVariables(): void
    {
        $data['assetsPath'] = '/../vendor/cubeta/cubeta-starter/src/Resources';

        View::share($data);
    }

    /**
     * load the used web views components so the user can use them
     * @return void
     */
    protected function loadComponents(): void
    {
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components/form');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components/form/checkboxes');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components/form/fields');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components/form/validation');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components/show');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components/images');
    }

    /**
     * publishing the package assets used to generate the web views
     * @return void
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/views/includes' => resource_path('views/includes'),
            __DIR__ . '/../resources/views/layout.blade.php' => resource_path('views/layout.blade.php'),
            __DIR__ . '/../resources/js' => resource_path('js'),
            __DIR__ . '/../resources/sass' => resource_path('sass'),
            __DIR__ . '/../public' => public_path(),
            __DIR__ . 'Commands/stubs/SetLocaleController.stub' => app_path('Http/Controllers/SetLocaleController.php')
        ], 'cubeta-starter-assets');
    }

    protected function registerSetLocaleRoute()
    {
        if (file_exists(base_path('app/Http/Controllers/SetLocaleController.php'))) {
            Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->middleware('web')->name('set-locale');
        } else {
            Route::post('/blank', function () {
                return response()->noContent();
            })->middleware('web')->name('set-locale');
        }
    }
}
