<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class ReactTSInertiaInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-react";
    public string $type = "installer";

    public function run(bool $override = false): void
    {
        if (!Settings::make()->installedWebPackages()) {
            CubeLog::add(new CubeError("Install React TS and Inertia packages first by running [php artisan cubeta:install react-ts-packages] and try again"));
            return;
        }

        $this->installInertia($override);

        $this->publishBaseRepository();
        $this->publishBaseService();
        $this->publishMakableTrait();
        $this->publishHasMediaTrait();

        $this->addAndRegisterAuthenticateMiddleware();

        $this->addSetLocalRoute();
        $this->addRouteFile(actor: 'public', container: ContainerType::WEB, version: $this->version);
        $this->addRouteFile(actor: 'protected', container: ContainerType::WEB, version: $this->version, middlewares: ["authenticated:web"]);
        FileUtils::registerMiddleware(
            "'locale' => AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("App\\Http\\Middleware\\AcceptedLanguagesMiddleware")
        );
        FileUtils::registerMiddleware(
            "HandleInertiaRequests::class",
            MiddlewareArrayGroupEnum::WEB,
            new PhpImportString("App\\Http\\Middleware\\HandleInertiaRequests")
        );

        $this->generateHomePage($override);
        $this->addIndexPageRoute();
        $this->generateSidebar($override);

        $this->registerHelpersFile();

        Settings::make()->setFrontendType(FrontendTypeEnum::REACT_TS);
        Settings::make()->setInstalledWeb();
    }

    private function installInertia(bool $override = false): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'react-ts',
            '--force' => $override,
        ]);

        CubeLog::add(Artisan::output());

        // adding the app layout blade file
        $this->generateFileFromStub(
            stubProperties: [],
            path: resource_path('/views/app.blade.php'),
            override: $override,
            otherStubsPath: CubePath::stubPath('Inertia/views/app-view.stub')
        );

        // adding inertia middleware
        $this->generateFileFromStub(
            stubProperties: [],
            path: app_path('/Http/Middleware/HandleInertiaRequests.php'),
            override: $override,
            otherStubsPath: CubePath::stubPath('Inertia/HandleInertiaRequestsMiddleware.stub')
        );

        CubeLog::add(new SuccessMessage("Your Frontend Stack Has Been Set To " . FrontendTypeEnum::REACT_TS->value));
    }

    public function generateHomePage(bool $override = false): void
    {
        $pagePath = CubePath::make('/resources/js/Pages/dashboard/Index.tsx');
        $pagePath->ensureDirectoryExists();
        $this->generateFileFromStub(
            [],
            $pagePath->fullPath,
            $override,
            CubePath::stubPath('Inertia/pages/dashboard.stub')
        );
    }

    private function generateSidebar(bool $override): void
    {
        $this->generateFileFromStub(
            ['{{index-route}}' => Routes::dashboardPage(Settings::make()->installedWebAuth())->name],
            CubePath::make('resources/js/Components/ui/Sidebar.tsx')->fullPath,
            $override,
            CubePath::stubPath('Inertia/components/Sidebar.stub')
        );
    }
}
