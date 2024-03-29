<?php

namespace Cubeta\CubetaStarter;

use Cubeta\CubetaStarter\Commands\CreatePivotTable;
use Cubeta\CubetaStarter\Commands\InitAuth;
use Cubeta\CubetaStarter\Commands\InitialProject;
use Cubeta\CubetaStarter\Commands\InstallWebPackages;
use Cubeta\CubetaStarter\Commands\MakeController;
use Cubeta\CubetaStarter\Commands\MakeExample;
use Cubeta\CubetaStarter\Commands\MakeFactory;
use Cubeta\CubetaStarter\Commands\MakeMigration;
use Cubeta\CubetaStarter\Commands\MakeModel;
use Cubeta\CubetaStarter\Commands\MakePostmanCollection;
use Cubeta\CubetaStarter\Commands\MakeRepository;
use Cubeta\CubetaStarter\Commands\MakeRequest;
use Cubeta\CubetaStarter\Commands\MakeResource;
use Cubeta\CubetaStarter\Commands\MakeSeeder;
use Cubeta\CubetaStarter\Commands\MakeService;
use Cubeta\CubetaStarter\Commands\MakeTest;
use Cubeta\CubetaStarter\Commands\MakeWebController;
use Cubeta\CubetaStarter\Commands\PublishAllAssetsCommand;
use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
        $this->publishWeb();
        $this->publishApi();
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
     * register route file of the package gui
     * @return void
     */
    private function registerGuiRoutesFile(): void
    {
        if (app()->environment('local')) {
            $this->loadRoutesFrom(__DIR__ . '/Routes/ui-routes.php');
        }
    }

    private function publishWeb(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cubeta-starter.php' => base_path('config/cubeta-starter.php'),
            __DIR__ . '/../pint.json' => base_path('pint.json'),
            __DIR__ . '/../lang/site.php' => lang_path('en/site.php'),
            __DIR__ . '/../resources/views' => resource_path('views'),
            __DIR__ . '/../resources/js' => resource_path('js'),
            __DIR__ . '/../resources/sass' => resource_path('sass'),
            __DIR__ . '/../public' => public_path(),
            __DIR__ . '/Traits/DataTablesTrait.php' => app_path("Traits/DataTablesTrait.php"),
            __DIR__ . '/../src/Providers' => app_path('/Providers'),
            __DIR__ . '/../resources/views/login.blade.php' => resource_path('views/login.blade.php'),
            __DIR__ . '/../resources/views/register.blade.php' => resource_path('views/register.blade.php'),
            __DIR__ . '/../resources/views/user-details.blade.php' => resource_path('views/user-details.blade.php'),
            __DIR__ . '/../resources/views/reset-password-request.blade.php' => resource_path('views/reset-password-request.blade.php'),
            __DIR__ . '/../resources/views/check-reset-code.blade.php' => resource_path('views/check-reset-code.blade.php'),
            __DIR__ . '/../resources/views/reset-password.blade.php' => resource_path('views/reset-password.blade.php'),
            __DIR__ . "/../src/Middleware/AcceptedLanguagesMiddleware.php" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),
            __DIR__ . "/../src/Rules/LanguageShape.php" => app_path("Rules/LanguageShape.php"),
            __DIR__ . '/../src/Traits/Translations.php' => app_path('Traits/Translations.php'),
            __DIR__ . "/../src/Casts/Translatable.php" => app_path('Casts/Translatable.php'),
            __DIR__ . '/Commands/stubs/SetLocaleController.stub' => app_path('Http/Controllers/SetLocaleController.php'),
            __DIR__ . '/app/Exceptions/handler.php' => base_path('/app/Exceptions/Handler.php'),
            __DIR__ . "/../src/Contracts/Repositories/BaseRepository.php" => app_path("Repositories/Contracts/BaseRepository.php"),
            __DIR__ . "/../src/Contracts/Repositories/IBaseRepository.php" => app_path("Repositories/Contracts/IBaseRepository.php"),
            __DIR__ . "/../src/Contracts/Services/BaseService.php" => app_path("Services/Contracts/BaseService.php"),
            __DIR__ . "/../src/Contracts/Services/IBaseService.php" => app_path("Services/Contracts/IBaseService.php"),
            __DIR__ . '/../src/Traits/FileHandler.php' => app_path("Traits/FileHandler.php"),
        ], 'cubeta-starter-web');
    }

    private function publishApi(): void
    {
        $this->publishes([
            __DIR__ . '/../src/Traits/RestTrait.php' => app_path('Traits/RestTrait.php'),
            __DIR__ . "/../src/Contracts/ApiController.php" => app_path("Http/Controllers/ApiController.php"),
            __DIR__ . '/app/Exceptions/handler.php' => base_path('/app/Exceptions/Handler.php'),
            __DIR__ . '/../config/cubeta-starter.php' => base_path('config/cubeta-starter.php'),
            __DIR__ . '/../pint.json' => base_path('pint.json'),
            __DIR__ . '/../lang/site.php' => lang_path('en/site.php'),
            __DIR__ . '/../src/Providers' => app_path('/Providers'),
            __DIR__ . "/../src/Middleware/AcceptedLanguagesMiddleware.php" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),
            __DIR__ . "/../src/Rules/LanguageShape.php" => app_path("Rules/LanguageShape.php"),
            __DIR__ . '/../src/Traits/Translations.php' => app_path('Traits/Translations.php'),
            __DIR__ . "/../src/Casts/Translatable.php" => app_path('Casts/Translatable.php'),
            __DIR__ . '/Commands/stubs/SetLocaleController.stub' => app_path('Http/Controllers/SetLocaleController.php'),
            __DIR__ . '/../src/Contracts/Tests/MainTestCase.php' => base_path("/tests/Contracts/MainTestCase.php"),
            __DIR__ . '/../src/Traits/TestHelpers.php' => app_path('/Traits/TestHelpers.php'),
            __DIR__ . "/../src/Contracts/Repositories/BaseRepository.php" => app_path("Repositories/Contracts/BaseRepository.php"),
            __DIR__ . "/../src/Contracts/Repositories/IBaseRepository.php" => app_path("Repositories/Contracts/IBaseRepository.php"),
            __DIR__ . "/../src/Contracts/Services/BaseService.php" => app_path("Services/Contracts/BaseService.php"),
            __DIR__ . "/../src/Contracts/Services/IBaseService.php" => app_path("Services/Contracts/IBaseService.php"),
            __DIR__ . '/../src/Traits/FileHandler.php' => app_path("Traits/FileHandler.php"),
        ], 'cubeta-starter-api');
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
            ->hasCommand(MakeWebController::class)
            ->hasCommand(InstallWebPackages::class)
            ->hasCommand(InitAuth::class)
            ->hasCommand(PublishAllAssetsCommand::class)
            ->hasCommand(MakeExample::class);
    }
}
