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
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Components\SidebarStubBuilder;
use Cubeta\CubetaStarter\Stub\Publisher;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class WebInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-web";

    public string $type = 'installer';

    public function run(): void
    {
        Settings::make()->setFrontendType(FrontendTypeEnum::BLADE);

        $this->publishBaseRepository();
        $this->publishBaseService();
        $this->publishMakableTrait();
        $this->publishHasMediaTrait();

        Artisan::call("vendor:publish", ['--force' => $this->override, "--tag" => "cubeta-starter-web"]);
        CubeLog::add(Artisan::output());

        $this->addAndRegisterAuthenticateMiddleware($this->override);

        $this->addRouteFile('public', ContainerType::WEB, $this->version);
        $this->addRouteFile(actor: 'protected', container: ContainerType::WEB, version: $this->version, middlewares: ["authenticated:web"]);
        $this->addSetLocalRoute();
        FileUtils::registerMiddleware(
            "'locale' => AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("App\\Http\\Middleware\\AcceptedLanguagesMiddleware")
        );
        $this->generateHomePage();
        $this->addIndexPageRoute();
        $this->generateSidebar();

        FileUtils::registerProvider("App\\Providers\\CubetaStarterServiceProvider::class");

        $this->registerHelpersFile();

        CubeLog::success("Your Frontend Stack Has Been Set To " . FrontendTypeEnum::BLADE->value);

        Settings::make()->setInstalledWeb();
        Settings::make()->setFrontendType(FrontendTypeEnum::BLADE);

        CubeLog::info("Don't forgot to install web packages by the GUI or by running [php artisan cubeta:install web-packages]");

        $this->removeTailwindCallFromViteConfig();
    }

    public function generateHomePage(): void
    {
        Publisher::make()
            ->source(CubePath::stubPath('Web/Blade/Views/Dashboard.stub'))
            ->destination(Views::dashboard()->path)
            ->publish($this->override);
    }

    private function generateSidebar(): void
    {
        $route = Routes::dashboardPage(Settings::make()->installedWebAuth())->name;
        SidebarStubBuilder::make()
            ->indexRoute($route)
            ->generate(CubePath::make('resources/views/includes/sidebar.blade.php'), $this->override);
    }

    private function removeTailwindCallFromViteConfig(): void
    {
        $vite = CubePath::make("vite.config.js");
        $content = $vite->getContent();
        $pattern = '/\s*tailwindcss\s*\((.*?)\)\s*(,)?/s';

        if (!preg_match($pattern, $content, $matches)) {
            return;
        }
        $content = preg_replace($pattern, '', $content);
        CubeLog::contentRemoved("tailwindcss()" , $vite->fullPath);

        $pattern = '#import\s*tailwindcss\s*from\s*["\']\s*@tailwindcss/vite\s*["\']\s*;#s';
        if (preg_match($pattern, $content, $matches)) {
            $content = preg_replace($pattern, '', $content);
            CubeLog::contentRemoved("import tailwindcss from '@tailwindcss/vite';" , $vite->fullPath);
        }

        $vite->putContent($content);
        $vite->format();
    }
}
