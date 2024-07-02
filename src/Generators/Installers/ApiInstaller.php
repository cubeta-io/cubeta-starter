<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class ApiInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-api";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        Artisan::call("vendor:publish", ['--force' => $override, "--tag" => "cubeta-starter-api"]);
        CubeLog::add(Artisan::output());

        $this->addRouteFile('public', version: $this->version);
        $this->addRouteFile('protected', version: $this->version);
        FileUtils::registerMiddleware(
            "'locale' => Middleware\\AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS
        );
    }
}
