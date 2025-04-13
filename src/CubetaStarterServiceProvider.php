<?php

namespace Cubeta\CubetaStarter;

use Cubeta\CubetaStarter\Commands\Generators\AddActor;
use Cubeta\CubetaStarter\Commands\Generators\MakeController;
use Cubeta\CubetaStarter\Commands\Generators\MakeFactory;
use Cubeta\CubetaStarter\Commands\Generators\MakeMigration;
use Cubeta\CubetaStarter\Commands\Generators\MakeModel;
use Cubeta\CubetaStarter\Commands\Generators\MakeRepository;
use Cubeta\CubetaStarter\Commands\Generators\MakeRequest;
use Cubeta\CubetaStarter\Commands\Generators\MakeResource;
use Cubeta\CubetaStarter\Commands\Generators\MakeSeeder;
use Cubeta\CubetaStarter\Commands\Generators\MakeService;
use Cubeta\CubetaStarter\Commands\Generators\MakeTest;
use Cubeta\CubetaStarter\Commands\Generators\MakeWebController;
use Cubeta\CubetaStarter\Commands\Installers\Installer;
use Cubeta\CubetaStarter\Commands\MakeExample;
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
        $this->publishInertiaReactTs();
        $this->publishAuthBladeViews();
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
            __DIR__ . '/../config/cubeta-starter.php'                             => base_path('config/cubeta-starter.php'),
            __DIR__ . '/../pint.json'                                             => base_path('pint.json'),
            __DIR__ . '/../lang/site.php'                                         => lang_path('en/site.php'),
            __DIR__ . '/../resources/views/blade/components'                      => resource_path('views/components'),
            __DIR__ . '/../resources/views/blade/includes'                        => resource_path('views/includes'),
            __DIR__ . '/../resources/views/blade/layout.blade.php'                => resource_path('views/layout.blade.php'),
            __DIR__ . '/../resources/js/blade'                                    => resource_path('js'),
            __DIR__ . '/../resources/css/blade'                                   => resource_path('css'),
            __DIR__ . '/../public'                                                => public_path(),
            __DIR__ . '/Traits/DataTablesTrait.stub'                              => app_path("Traits/DataTablesTrait.php"),
            __DIR__ . '/../src/Providers/Blade/CubetaStarterServiceProvider.stub' => app_path('/Providers/CubetaStarterServiceProvider.php'),
            __DIR__ . "/../src/Middleware/AcceptedLanguagesMiddleware.stub"       => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),
            __DIR__ . "/../src/Rules/LanguageShape.stub"                          => app_path("Rules/LanguageShape.php"),
            __DIR__ . "/../src/Casts/Translatable.stub"                           => app_path('Casts/Translatable.php'),
            __DIR__ . "/../src/App/Serializers/Translatable.stub"                 => app_path("Serializers/Translatable.php"),
            __DIR__ . '/stubs/SetLocaleController.stub'                           => app_path('Http/Controllers/SetLocaleController.php'),
            __DIR__ . '/App/Exceptions/handler.stub'                              => base_path('/app/Exceptions/Handler.php'),
            __DIR__ . "/../src/Contracts/Repositories/BaseRepository.stub"        => app_path("Repositories/Contracts/BaseRepository.php"),
            __DIR__ . "/../src/Contracts/Services/BaseService.stub"               => app_path("Services/Contracts/BaseService.php"),
            __DIR__ . "/../src/Contracts/BulkActions/BaseBulkAction.stub"         => app_path("BulkAction/BaseBulkAction.php"),
            __DIR__ . "/../src/Contracts/Services/Makable.stub"                   => app_path("Services/Contracts/Makable.php"),
            __DIR__ . "/../src/Contracts/Excel/BaseExporter.stub"                 => app_path("Excel/BaseExporter.php"),
            __DIR__ . '/../src/Contracts/Excel/BaseImporter.stub'                 => app_path("Excel/BaseImporter.php"),
            __DIR__ . '/../src/Traits/FileHandler.stub'                           => app_path("Traits/FileHandler.php"),
            __DIR__ . '/../src/stubs/casts/MediaCast.stub'                  => app_path("Casts/MediaCast.php"),
        ], 'cubeta-starter-web');
    }

    public function publishAuthBladeViews(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/views/blade/login.blade.php'                  => resource_path('views/login.blade.php'),
            __DIR__ . '/../resources/views/blade/register.blade.php'               => resource_path('views/register.blade.php'),
            __DIR__ . '/../resources/views/blade/user-details.blade.php'           => resource_path('views/user-details.blade.php'),
            __DIR__ . '/../resources/views/blade/reset-password-request.blade.php' => resource_path('views/reset-password-request.blade.php'),
            __DIR__ . '/../resources/views/blade/check-reset-code.blade.php'       => resource_path('views/check-reset-code.blade.php'),
            __DIR__ . '/../resources/views/blade/reset-password.blade.php'         => resource_path('views/reset-password.blade.php'),
        ], 'cubeta-auth-views');
    }

    private function publishApi(): void
    {
        $this->publishes([
            __DIR__ . "/../src/Contracts/ApiController.stub"                => app_path("Http/Controllers/ApiController.php"),
            __DIR__ . '/../src/stubs/handler.stub'                          => base_path('/app/Exceptions/Handler.php'),
            __DIR__ . '/../config/cubeta-starter.php'                       => base_path('config/cubeta-starter.php'),
            __DIR__ . '/../pint.json'                                       => base_path('pint.json'),
            __DIR__ . '/../lang/site.php'                                   => lang_path('en/site.php'),
            __DIR__ . "/../src/Middleware/AcceptedLanguagesMiddleware.stub" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),
            __DIR__ . "/../src/Rules/LanguageShape.stub"                    => app_path("Rules/LanguageShape.php"),
            __DIR__ . "/../src/Casts/Translatable.stub"                     => app_path('Casts/Translatable.php'),
            __DIR__ . "/../src/App/Serializers/Translatable.stub"           => app_path("Serializers/Translatable.php"),
            __DIR__ . '/../src/Contracts/Tests/MainTestCase.stub'           => base_path("/tests/Contracts/MainTestCase.php"),
            __DIR__ . '/../src/Traits/TestHelpers.stub'                     => app_path('/Traits/TestHelpers.php'),
            __DIR__ . "/../src/Contracts/Repositories/BaseRepository.stub"  => app_path("Repositories/Contracts/BaseRepository.php"),
            __DIR__ . "/../src/Contracts/Services/BaseService.stub"         => app_path("Services/Contracts/BaseService.php"),
            __DIR__ . "/../src/Contracts/BulkActions/BaseBulkAction.stub"   => app_path("BulkAction/BaseBulkAction.php"),
            __DIR__ . "/../src/Contracts/Services/Makable.stub"             => app_path("Services/Contracts/Makable.php"),
            __DIR__ . "/../src/Contracts/Excel/BaseExporter.stub"           => app_path("Excel/BaseExporter.php"),
            __DIR__ . '/../src/Contracts/Excel/BaseImporter.stub'           => app_path("Excel/BaseImporter.php"),
            __DIR__ . '/../src/Traits/FileHandler.stub'                     => app_path("Traits/FileHandler.php"),
            __DIR__ . '/../src/app/Http/Resources/BaseResource.stub'        => app_path('Http/Resources/BaseResource.php'),
            __DIR__ . '/../src/stubs/casts/MediaCast.stub'                  => app_path("Casts/MediaCast.php"),
            __DIR__ . '/../src/stubs/Modules/ApiResponse.stub'              => app_path('Modules/ApiResponse.php'),
            __DIR__ . '/../src/stubs/Helpers/helpers.stub'                  => app_path('Helpers/helpers.php'),
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
            ->hasCommand(AddActor::class)
            ->hasCommand(MakeWebController::class)
            ->hasCommand(Installer::class)
            ->hasCommand(MakeExample::class);
    }

    public function publishInertiaReactTs(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cubeta-starter.php'                      => base_path('config/cubeta-starter.php'),
            __DIR__ . '/../pint.json'                                      => base_path('pint.json'),
            __DIR__ . '/../lang/site.php'                                  => lang_path('en/site.php'),
            __DIR__ . '/stubs/Inertia/configurations/postcss.config.stub'  => base_path('/postcss.config.js'),
            __DIR__ . '/stubs/Inertia/configurations/tailwind.config.stub' => base_path('/tailwind.config.js'),
            __DIR__ . '/stubs/Inertia/configurations/tsconfig.stub'        => base_path('/tsconfig.json'),
            __DIR__ . '/stubs/Inertia/configurations/vite.config.stub'     => base_path('/vite.config.js'),

            __DIR__ . '/../resources/js/inertia/Components'         => resource_path('/js/Components'),
            __DIR__ . '/../resources/js/inertia/Hooks'              => resource_path('/js/Hooks'),
            __DIR__ . '/../resources/js/inertia/Contexts'           => resource_path('/js/Contexts'),
            __DIR__ . '/../resources/js/inertia/Models'             => resource_path('/js/Models'),
            __DIR__ . '/../resources/js/inertia/types'              => resource_path('/js/types'),
            __DIR__ . '/../resources/js/inertia/cubeta-starter.tsx' => resource_path('/js/cubeta-starter.tsx'),
            __DIR__ . '/../resources/js/inertia/helper.ts'          => resource_path('/js/helper.ts'),
            __DIR__ . '/../resources/css/inertia'                   => resource_path('/css'),
            __DIR__ . '/../public/images'                           => public_path('/images'),

            __DIR__ . '/stubs/SetLocaleController.stub'                     => app_path('Http/Controllers/SetLocaleController.php'),
            __DIR__ . "/../src/Middleware/AcceptedLanguagesMiddleware.stub" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),
            __DIR__ . "/../src/Rules/LanguageShape.stub"                    => app_path("Rules/LanguageShape.php"),
            __DIR__ . "/../src/Casts/Translatable.stub"                     => app_path('Casts/Translatable.php'),
            __DIR__ . "/../src/App/Serializers/Translatable.stub"           => app_path("Serializers/Translatable.php"),
            __DIR__ . "/../src/Contracts/Repositories/BaseRepository.stub"  => app_path("Repositories/Contracts/BaseRepository.php"),
            __DIR__ . "/../src/Contracts/Services/BaseService.stub"         => app_path("Services/Contracts/BaseService.php"),
            __DIR__ . "/../src/Contracts/BulkActions/BaseBulkAction.stub"   => app_path("BulkAction/BaseBulkAction.php"),
            __DIR__ . "/../src/Contracts/Services/Makable.stub"             => app_path("Services/Contracts/Makable.php"),
            __DIR__ . "/../src/Contracts/Excel/BaseExporter.stub"           => app_path("Excel/BaseExporter.php"),
            __DIR__ . '/../src/Contracts/Excel/BaseImporter.stub'           => app_path("Excel/BaseImporter.php"),
            __DIR__ . '/../src/Traits/FileHandler.stub'                     => app_path("Traits/FileHandler.php"),
            __DIR__ . '/../src/stubs/casts/MediaCast.stub'                  => app_path("Casts/MediaCast.php"),
        ], 'react-ts');
    }
}
