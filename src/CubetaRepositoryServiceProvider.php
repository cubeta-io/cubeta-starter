<?php

namespace Cubeta\CubetaStarter;

use Cubeta\CubetaStarter\Commands\CreatePivotTable;
use Cubeta\CubetaStarter\Commands\InitialProject;
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
use Cubeta\CubetaStarter\Commands\ModelMakeCommand;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CubetaRepositoryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('repository')
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
            ->hasCommand(MakeWebController::class)
            ->hasCommand(MakePolicy::class);
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
            $this->mergeConfigFrom($this->package->basePath("/../config/{$configFileName}.php"), $configFileName);
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/repository.php', 'repository');

        $this->packageRegistered();

        $this->overrideCommands();

        return $this;
    }

    public function overrideCommands()
    {
        $this->app->extend('command.model.make', function () {
            return app()->make(ModelMakeCommand::class);
        });
    }

    public function boot()
    {
        parent::boot();

        $this->loadComponents();
        $this->publishAssets();
    }


    /**
     * @return void
     */
    protected function loadComponents(): void
    {
        Blade::componentNamespace('Cubeta\\CubetaStarter\\Components', 'cubeta-starter');
    }

    protected function publishAssets()
    {
        $this->publishes([
            __DIR__ . '/../resources/views/includes' => resource_path('views/includes'),
            __DIR__ . '/../resources/views/layout.blade.php' => resource_path('views/layout.blade.php'),
            __DIR__ . '/../resources/js' => resource_path('js'),
            __DIR__ . '/../resources/sass' => resource_path('sass'),
            __DIR__ . '/../public' => public_path(),
        ], 'cubeta-starter-assets');
    }
}
