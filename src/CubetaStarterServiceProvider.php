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
     * register the route file of the package gui
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
            __DIR__ . '/../.prettierignore' => base_path('.prettierignore'),
            __DIR__ . '/../lang/site.php' => lang_path('en/site.php'),

            __DIR__ . '/../resources/views/blade/components' => resource_path('views/components'),
            __DIR__ . '/../resources/views/blade/includes' => resource_path('views/includes'),
            __DIR__ . '/../resources/views/blade/layout.blade.php' => resource_path('views/layout.blade.php'),
            __DIR__ . '/../resources/js/blade' => resource_path('js'),
            __DIR__ . '/../resources/css/blade' => resource_path('css'),
            __DIR__ . '/../public' => public_path(),

            __DIR__ . '/../src/Stub/stubs/Traits/DataTablesTrait.stub' => app_path("Traits/DataTablesTrait.php"),

            __DIR__ . '/../src/Stub/stubs/Web/Blade/Providers/CubetaStarterServiceProvider.stub' => app_path('/Providers/CubetaStarterServiceProvider.php'),

            __DIR__ . "/../src/Stub/stubs/Middlewares/AcceptedLanguagesMiddleware.stub" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),

            __DIR__ . '/../src/Stub/stubs/Rules/ValidTranslatableJson.stub' => app_path('Rules/ValidTranslatableJson.php'),

            __DIR__ . '/../src/Stub/stubs/Controllers/WebController.stub' => app_path('Http/Controllers/WebController.php'),
            __DIR__ . '/../src/Stub/stubs/Controllers/SetLocaleController.stub' => app_path('Http/Controllers/SetLocaleController.php'),

            __DIR__ . "/../src/Stub/stubs/Casts/Translatable.stub" => app_path('Casts/Translatable.php'),
            __DIR__ . '/../src/Stub/stubs/Casts/MediaCast.stub' => app_path("Casts/MediaCast.php"),

            __DIR__ . "/../src/Stub/stubs/Serializers/Translatable.stub" => app_path("Serializers/Translatable.php"),

            __DIR__ . "/../src/Stub/stubs/BulkActions/BaseBulkAction.stub" => app_path("BulkAction/BaseBulkAction.php"),

            __DIR__ . "/../src/Stub/stubs/Excel/BaseExporter.stub" => app_path("Excel/BaseExporter.php"),
            __DIR__ . '/../src/Stub/stubs/Excel/BaseImporter.stub' => app_path("Excel/BaseImporter.php"),

            __DIR__ . '/../src/Stub/stubs/Helpers/helpers.stub' => app_path('Helpers/helpers.php'),
        ], 'cubeta-starter-web');
    }

    private function publishApi(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cubeta-starter.php' => base_path('config/cubeta-starter.php'),
            __DIR__ . '/../pint.json' => base_path('pint.json'),
            __DIR__ . '/../lang/site.php' => lang_path('en/site.php'),

            __DIR__ . "/../src/Stub/stubs/Controllers/ApiController.stub" => app_path("Http/Controllers/ApiController.php"),

            __DIR__ . "/../src/Stub/stubs/Middlewares/AcceptedLanguagesMiddleware.stub" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),

            __DIR__ . '/../src/Stub/stubs/Rules/ValidTranslatableJson.stub' => app_path('Rules/ValidTranslatableJson.php'),

            __DIR__ . "/../src/Stub/stubs/Casts/Translatable.stub" => app_path('Casts/Translatable.php'),
            __DIR__ . '/../src/Stub/stubs/Casts/MediaCast.stub' => app_path("Casts/MediaCast.php"),

            __DIR__ . "/../src/Stub/stubs/Serializers/Translatable.stub" => app_path("Serializers/Translatable.php"),
            __DIR__ . "/../src/Stub/stubs/BulkActions/BaseBulkAction.stub" => app_path("BulkAction/BaseBulkAction.php"),

            __DIR__ . "/../src/Stub/stubs/Excel/BaseExporter.stub" => app_path("Excel/BaseExporter.php"),
            __DIR__ . '/../src/Stub/stubs/Excel/BaseImporter.stub' => app_path("Excel/BaseImporter.php"),

            __DIR__ . '/../src/Stub/stubs/Resources/BaseResource.stub' => app_path('Http/Resources/BaseResource/BaseResource.php'),
            __DIR__ . '/../src/Stub/stubs/Resources/AnonymousResourceCollection.stub' => app_path('Http/Resources/BaseResource/AnonymousResourceCollection.php'),

            __DIR__ . '/../src/Stub/stubs/Modules/ApiResponse.stub' => app_path('Modules/ApiResponse.php'),

            __DIR__ . '/../src/Stub/stubs/Helpers/helpers.stub' => app_path('Helpers/helpers.php'),
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
            ->hasConfigFile([
                'cubeta-starter',
                'views-names'
            ])
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
            __DIR__ . '/../config/cubeta-starter.php' => base_path('config/cubeta-starter.php'),
            __DIR__ . '/../pint.json' => base_path('pint.json'),
            __DIR__ . '/../.prettierignore' => base_path('.prettierignore'),
            __DIR__ . '/../lang/site.php' => lang_path('en/site.php'),
            __DIR__ . '/../src/Stub/stubs/Web/InertiaReact/Config/TsConfig.stub' => base_path('/tsconfig.json'),
            __DIR__ . '/../src/Stub/stubs/Web/InertiaReact/Config/ViteConfig.stub' => base_path('/vite.config.js'),

            __DIR__ . '/../resources/js/inertia/Components' => resource_path('/js/Components'),
            __DIR__ . '/../resources/js/inertia/Hooks' => resource_path('/js/Hooks'),
            __DIR__ . '/../resources/js/inertia/Contexts' => resource_path('/js/Contexts'),
            __DIR__ . '/../resources/js/inertia/Models' => resource_path('/js/Models'),
            __DIR__ . '/../resources/js/inertia/types' => resource_path('/js/types'),
            __DIR__ . '/../resources/js/inertia/cubeta-starter.tsx' => resource_path('/js/cubeta-starter.tsx'),
            __DIR__ . '/../resources/js/inertia/helper.ts' => resource_path('/js/helper.ts'),
            __DIR__ . '/../resources/js/inertia/Modules' => resource_path('/js/Modules'),
            __DIR__ . '/../resources/css/inertia' => resource_path('/css'),
            __DIR__ . '/../public/images' => public_path('/images'),

            __DIR__ . '/../src/Stub/stubs/Controllers/WebController.stub' => app_path('/Http/Controllers/WebController.php'),
            __DIR__ . '/../src/Stub/stubs/Controllers/SetLocaleController.stub' => app_path('Http/Controllers/SetLocaleController.php'),

            __DIR__ . "/../src/Stub/stubs/Middlewares/AcceptedLanguagesMiddleware.stub" => app_path("Http/Middleware/AcceptedLanguagesMiddleware.php"),

            __DIR__ . '/../src/Stub/stubs/Rules/ValidTranslatableJson.stub' => app_path('Rules/ValidTranslatableJson.php'),

            __DIR__ . "/../src/Stub/stubs/Casts/Translatable.stub" => app_path('Casts/Translatable.php'),
            __DIR__ . '/../src/Stub/stubs/Casts/MediaCast.stub' => app_path("Casts/MediaCast.php"),

            __DIR__ . "/../src/Stub/stubs/Serializers/Translatable.stub" => app_path("Serializers/Translatable.php"),

            __DIR__ . "/../src/Stub/stubs/BulkActions/BaseBulkAction.stub" => app_path("BulkAction/BaseBulkAction.php"),

            __DIR__ . "/../src/Stub/stubs/Excel/BaseExporter.stub" => app_path("Excel/BaseExporter.php"),
            __DIR__ . '/../src/Stub/stubs/Excel/BaseImporter.stub' => app_path("Excel/BaseImporter.php"),

            __DIR__ . '/../src/Stub/stubs/Resources/BaseResource.stub' => app_path('Http/Resources/BaseResource/BaseResource.php'),
            __DIR__ . '/../src/Stub/stubs/Resources/AnonymousResourceCollection.stub' => app_path('Http/Resources/BaseResource/AnonymousResourceCollection.php'),

            __DIR__ . '/../src/Stub/stubs/Modules/ApiResponse.stub' => app_path('Modules/ApiResponse.php'),
            __DIR__ . '/../src/Stub/stubs/Helpers/helpers.stub' => app_path('Helpers/helpers.php'),
        ], 'react-ts');
    }
}
