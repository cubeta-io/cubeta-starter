<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class ApiInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-api";

    public function run(bool $override = false): void
    {
        Artisan::call("vendor:publish --tag=cubeta-starter-api", ['--force' => $override]);
        CubeLog::add(Artisan::output());

        $this->addRouteFile('public');
        $this->addRouteFile('protected');
    }
}
