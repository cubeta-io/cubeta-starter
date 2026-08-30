<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Modules\Views;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Components\NavMainStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Components\SidebarStubBuilder;
use Cubeta\CubetaStarter\Stub\Publisher;
use Cubeta\CubetaStarter\Traits\RouteBinding;

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

        $this->installValidationPackages();

        $this->publishTsConfig();
        $this->installInertia();
        $this->installShadcn();

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
        FileUtils::executeCommandInTheBaseDirectory(
            str("php artisan vendor:publish --tag=react-ts")
                ->when(
                    $this->override,
                    fn($s) => $s->append(" --force")
                )
        );

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
        NavMainStubBuilder::make()
            ->indexRoute(Routes::dashboardPage(Settings::make()->installedWebAuth())->name)
            ->generate(CubePath::make('resources/js/components/dashboard/sidebar/nav-main.tsx'), $this->override);

        SidebarStubBuilder::make()
            ->indexRoute(Routes::dashboardPage(Settings::make()->installedWebAuth())->name)
            ->generate(CubePath::make('resources/js/components/dashboard/sidebar/app-sidebar.tsx'), $this->override);
    }

    public function installShadcn(): void
    {
        $componentsJsonPath = CubePath::make("components.json");
        if ($componentsJsonPath->exist() && !$this->override) {
            $componentsJsonPath->logAlreadyExist("Installing web inertia react stack packages");
        } else {
            Publisher::make()
                ->source(CubePath::stubPath("Web/InertiaReact/Config/ComponentsJson.stub"))
                ->destination($componentsJsonPath)
                ->publish($this->override);
        }

        PackageManager::npx("shadcn@latest init -t laravel -b base -y -p nova -f --rtl --pointer --reinstall --css-variables");

        PackageManager::shadcnAdd([
            "alert-dialog",
            "avatar",
            "badge",
            "breadcrumb",
            "button",
            "card",
            "chart",
            "checkbox",
            "collapsible",
            "dialog",
            "drawer",
            "dropdown-menu",
            "field",
            "input",
            "label",
            "radio-group",
            "select",
            "separator",
            "sheet",
            "sidebar",
            "skeleton",
            "sonner",
            "table",
            "tabs",
            "textarea",
            "toggle",
            "toggle-group",
            "tooltip",
            'popover',
            'calendar'
        ], $this->override);
    }

    public function publishTsConfig(): void
    {
        Publisher::make()
            ->source(CubePath::stubPath('Web/InertiaReact/Config/TsConfig.stub'))
            ->destination(CubePath::make('tsconfig.json'))
            ->publish($this->override);
    }
}
