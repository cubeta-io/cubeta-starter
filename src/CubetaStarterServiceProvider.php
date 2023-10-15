<?php

namespace Cubeta\CubetaStarter;

use Cubeta\CubetaStarter\Commands\InitAuth;
use Cubeta\CubetaStarter\Commands\PublishAllAssetsCommand;
use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Package;
use Cubeta\CubetaStarter\Commands\MakeTest;
use Cubeta\CubetaStarter\Commands\MakeModel;
use Cubeta\CubetaStarter\Commands\MakePolicy;
use Cubeta\CubetaStarter\Commands\MakeSeeder;
use Cubeta\CubetaStarter\Commands\MakeFactory;
use Cubeta\CubetaStarter\Commands\MakeRequest;
use Cubeta\CubetaStarter\Commands\MakeService;
use Cubeta\CubetaStarter\Commands\MakeResource;
use Cubeta\CubetaStarter\Commands\MakeMigration;
use Cubeta\CubetaStarter\Commands\InitialProject;
use Cubeta\CubetaStarter\Commands\MakeController;
use Cubeta\CubetaStarter\Commands\MakeRepository;
use Cubeta\CubetaStarter\Commands\CreatePivotTable;
use Cubeta\CubetaStarter\Commands\MakeWebController;
use Cubeta\CubetaStarter\Commands\InstallWebPackages;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Cubeta\CubetaStarter\Commands\MakePostmanCollection;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;

class CubetaStarterServiceProvider extends PackageServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        // loaded from the package
        $this->loadGuiViews();
        $this->loadGuiViewsVariables();

        // register the route files
        $this->registerGuiRoutesFile();

        // publishes
        $this->publishConfigFiles();
        $this->publishExceptionHandler();
        $this->publishAssets();
        $this->publishAuthViews();
        $this->publishRepositoriesContracts();
        $this->publishServices();
        $this->publishApiControllerClass();
        $this->publishMiddlewares();
        $this->publishValidationRules();
        $this->publishTraits();
        $this->publishProviders();
    }

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
            ->hasCommand(InstallWebPackages::class)
            ->hasCommand(InitAuth::class)
            ->hasCommand(PublishAllAssetsCommand::class);
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

    /**
     * publishing the package assets used to generate the web views
     * @return void
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views'),
            __DIR__ . '/../resources/js' => resource_path('js'),
            __DIR__ . '/../resources/sass' => resource_path('sass'),
            __DIR__ . '/../public' => public_path(),
            __DIR__ . '/Commands/stubs/SetLocaleController.stub' => app_path('Http/Controllers/SetLocaleController.php')
        ], 'cubeta-starter-assets');
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
     * load the variables used in the GUI views
     * @return void
     */
    private function loadGuiViewsVariables(): void
    {
        $data['assetsPath'] = '/../vendor/cubeta/cubeta-starter/src/Resources';

        View::share($data);
    }

    /**
     * publish the package config file
     * @return void
     */
    private function publishConfigFiles(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cubeta-starter.php' => base_path('config') . '/cubeta-starter.php',
            __DIR__ . '/../pint.json' => base_path('pint.json'),
            __DIR__ . '/../lang/site.php' => lang_path('en/site.php'),
        ], 'cubeta-starter-config');
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
     * register route file of the package gui
     * @return void
     */
    private function registerGuiRoutesFile(): void
    {
        if (app()->environment('local')) {
            $this->loadRoutesFrom(__DIR__ . '/Routes/ui-routes.php');
        }
    }

    public function publishAuthViews(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/views/login.blade.php' => resource_path('views/login.blade.php'),
            __DIR__ . '/../resources/views/register.blade.php' => resource_path('views/register.blade.php'),
            __DIR__ . '/../resources/views/user-details.blade.php' => resource_path('views/user-details.blade.php'),
            __DIR__ . '/../resources/views/reset-password-request.blade.php' => resource_path('views/reset-password-request.blade.php'),
            __DIR__ . '/../resources/views/check-reset-code.blade.php' => resource_path('views/check-reset-code.blade.php'),
            __DIR__ . '/../resources/views/reset-password.blade.php' => resource_path('views/reset-password.blade.php'),
        ], 'cubeta-auth-views');
    }

    public function publishRepositoriesContracts(): void
    {
        $this->publishes([
            __DIR__ . "/../src/Contracts/Repositories/BaseRepository.php" => app_path("Repositories/Contracts/BaseRepository.php"),
            __DIR__ . "/../src/Contracts/Repositories/IBaseRepository.php" => app_path("Repositories/Contracts/IBaseRepository.php")
        ], 'cubeta-starter-repositories');
    }

    public function publishServices(): void
    {
        $this->publishes([
            __DIR__ . "/../src/Contracts/Services/BaseService.php" => app_path("Services/Contracts/BaseService.php"),
            __DIR__ . "/../src/Contracts/Services/IBaseService.php" => app_path("Services/Contracts/IBaseService.php")
        ], 'cubeta-starter-services');
    }

    public function publishApiControllerClass(): void
    {
        $this->publishes([
            __DIR__ . "/../src/Contracts/ApiController.php" => app_path("Http/Controllers/ApiController.php")
        ], 'cubeta-starter-api-controller');
    }

    public function publishMiddlewares(): void
    {
        $this->publishes([
            __DIR__ . "/../src/Middleware/AcceptedLanguagesMiddleware.php" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),
        ], "cubeta-starter-middlewares");
    }

    public function publishValidationRules(): void
    {
        $this->publishes([
            __DIR__ . "/../src/Rules/LanguageShape.php" => app_path("Rules/LanguageShape.php"),
        ], 'cubeta-starter-validation-rules');
    }

    public function publishTraits(): void
    {
        $this->publishes([
            __DIR__ . '/../src/Traits/FileHandler.php' => app_path("Traits/FileHandler.php"),
            __DIR__ . '/../src/Traits/MainTestCase.php' => app_path("Traits/MainTestCase.php"),
            __DIR__ . '/../src/Traits/PushNotificationHelper.php' => app_path('Traits/PushNotificationHelper.php'),
            __DIR__ . '/../src/Traits/RestTrait.php' => app_path('Traits/RestTrait.php'),
            __DIR__ . '/../src/Traits/TestHelpers.php' => app_path('/Traits/TestHelpers.php'),
            __DIR__ . '/../src/Traits/Translations.php' => app_path('Traits/Translations.php'),
        ], 'cubeta-starter-traits');
    }

    public function publishProviders(): void
    {
        $this->publishes([
            __DIR__ . '/../src/Providers' => app_path('/Providers')
        ], 'cubeta-starter-providers');
    }
}
