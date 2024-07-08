<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;
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

        $this->addRouteFile('public', ContainerType::WEB, $this->version);
        $this->addRouteFile('protected', ContainerType::WEB, $this->version);
        $this->addSetLocalRoute();
        FileUtils::registerMiddleware(
            "'locale' => App\\Http\\Middleware\\AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS
        );
        FileUtils::registerProvider("App\\Providers\\CubetaStarterServiceProvider::class");
        CubeLog::add(new SuccessMessage("Your Frontend Stack Has Been Set To " . FrontendTypeEnum::BLADE->value));
    }
}
