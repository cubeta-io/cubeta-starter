<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Modules\Views;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Components\SidebarStubBuilder;
use Cubeta\CubetaStarter\Stub\Publisher;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class ReactTSInertiaInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-react";
    public string $type = "installer";

    public function run(): void
    {
        if (!Settings::make()->installedWebPackages()) {
            CubeLog::error("Install React TS and Inertia packages first by running [php artisan cubeta:install react-ts-packages] and try again");
            return;
        }

        $this->installInertia();

        $this->publishBaseRepository();
        $this->publishBaseService();
        $this->publishMakableTrait();
        $this->publishHasMediaTrait();

        $this->addAndRegisterAuthenticateMiddleware($this->override);

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

        $this->generateHomePage();
        $this->addIndexPageRoute();
        $this->generateSidebar();

        $this->registerHelpersFile();

        Settings::make()->setFrontendType(FrontendTypeEnum::REACT_TS);
        Settings::make()->setInstalledWeb();
    }

    private function installInertia(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'react-ts',
            '--force' => $this->override,
        ]);

        CubeLog::add(Artisan::output());

        Publisher::make()
            ->source(CubePath::stubPath('/Web/InertiaReact/Views/App.stub'))
            ->destination(CubePath::make("/resources/views/app.blade.php"))
            ->publish($this->override);

        Publisher::make()
            ->source(CubePath::stubPath('Middlewares/HandleInertiaRequestsMiddleware.stub'))
            ->destination(CubePath::make('app/Http/Middleware/HandleInertiaRequests.php'))
            ->publish($this->override);
        CubeLog::success("Your Frontend Stack Has Been Set To " . FrontendTypeEnum::REACT_TS->value);
    }

    public function generateHomePage(): void
    {
        Publisher::make()
            ->source(CubePath::stubPath('Web/InertiaReact/Pages/Dashboard.stub'))
            ->destination(Views::dashboard()->path)
            ->publish($this->override);
    }

    private function generateSidebar(): void
    {
        SidebarStubBuilder::make()
            ->indexRoute(Routes::dashboardPage(Settings::make()->installedWebAuth())->name)
            ->generate(CubePath::make('resources/js/Components/ui/Sidebar.tsx'), $this->override);
    }
}
