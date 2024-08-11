<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeInfo;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class WebInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-web";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        Settings::make()->setFrontendType(FrontendTypeEnum::BLADE);

        Artisan::call("vendor:publish", ['--force' => $override, "--tag" => "cubeta-starter-web"]);
        CubeLog::add(Artisan::output());

        $this->addAndRegisterAuthenticateMiddleware($override);

        $this->addRouteFile('public', ContainerType::WEB, $this->version);
        $this->addRouteFile(actor: 'protected', container: ContainerType::WEB, version: $this->version, middlewares: ["authenticated"]);
        $this->addSetLocalRoute();
        FileUtils::registerMiddleware(
            "'locale' => AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            "use App\\Http\\Middleware\\AcceptedLanguagesMiddleware;"
        );
        $this->generateSidebar($override);
        $this->generateHomePage($override);
        FileUtils::registerProvider("App\\Providers\\CubetaStarterServiceProvider::class");
        CubeLog::add(new SuccessMessage("Your Frontend Stack Has Been Set To " . FrontendTypeEnum::BLADE->value));
        Settings::make()->setInstalledWeb();
        Settings::make()->setFrontendType(FrontendTypeEnum::BLADE);
        CubeLog::add(new CubeInfo("Don't forgot to install web packages by the GUI or by running [php artisan cubeta:install web-packages]"));
    }

    public function generateHomePage(bool $override = false): void
    {
        $viewPath = CubePath::make('/resources/views/dashboard/index.blade.php');
        $viewPath->ensureDirectoryExists();
        $this->generateFileFromStub(
            [],
            $viewPath->fullPath,
            $override,
            CubePath::stubPath('views/home.stub')
        );

        $publicRoutFilePath = $this->getRouteFilePath(ContainerType::WEB, "protected", $this->version);
        $content = $publicRoutFilePath->getContent();
        $route = $this->getWebIndexPageRoute("protected", FrontendTypeEnum::BLADE);
        if (!FileUtils::contentExistInFile($publicRoutFilePath, $route)) {
            $content .= "\n$route\n";
        } else {
            return;
        }

        $publicRoutFilePath->putContent($content);
        $publicRoutFilePath->format();
    }

    private function generateSidebar(bool $override): void
    {
        $this->generateFileFromStub(
            [
                '{{index-route}}' => $this->getWebIndexPageRoute(actor: 'protected', justName: true),
            ],
            CubePath::make('resources/views/includes/sidebar.blade.php')->fullPath,
            $override,
            CubePath::stubPath('views/sidebar.stub')
        );
    }
}
