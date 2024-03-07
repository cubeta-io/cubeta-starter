<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class WebInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-web";

    public function run(bool $override = false): void
    {
        Artisan::call("vendor:publish", ['--force' => $override, "--tag" => "cubeta-starter-web"]);
        CubeLog::add(Artisan::output());

        $this->addRouteFile('public', ContainerType::WEB);
        $this->addRouteFile('protected', ContainerType::WEB);
    }
}
