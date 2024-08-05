<?php

namespace Cubeta\CubetaStarter\Commands\Installers;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Generators\Installers\ApiInstaller;
use Cubeta\CubetaStarter\Generators\Installers\AuthInstaller;
use Cubeta\CubetaStarter\Generators\Installers\BladePackagesInstaller;
use Cubeta\CubetaStarter\Generators\Installers\PermissionsInstaller;
use Cubeta\CubetaStarter\Generators\Installers\ReactTSInertiaInstaller;
use Cubeta\CubetaStarter\Generators\Installers\ReactTsPackagesInstaller;
use Cubeta\CubetaStarter\Generators\Installers\WebInstaller;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\CubeWarning;

class Installer extends BaseCommand
{
    protected $description = 'Add Package Files For Api Based Usage';

    protected $signature = 'cubeta:install {name : plugin name [api , web , web-packages , auth , permissions , react-ts , react-ts-packages]} {version=v1} {--force}';

    public function handle(): void
    {
        $plugin = $this->argument('name');
        $version = $this->argument('version');
        $plugins = ['api', 'web', 'web-packages', 'auth', 'permissions', 'react-ts', 'react-ts-packages'];

        if (!in_array($plugin, $plugins)) {
            $this->error("Invalid Input");
            $this->warn("Installed Plugin Should Be One Of The Following : " . collect($plugins)->toJson());
            return;
        }

        $override = $this->option('force') ?? null;

        if (!$override) {
            $override = $this->askForOverride();
        }

        switch ($plugin) {
            case "api" :
                $gen = new GeneratorFactory(ApiInstaller::$key);
                break;
            case "web" :
                $gen = new GeneratorFactory(WebInstaller::$key);
                break;
            case "web-packages" :
                $gen = new GeneratorFactory(BladePackagesInstaller::$key);
                break;
            case "permissions" :
                $gen = new GeneratorFactory(PermissionsInstaller::$key);
                break;
            case "auth" :
                $container = $this->askForContainer();
                $gen = new GeneratorFactory(AuthInstaller::$key);
                break;
            case "react-ts" :
                $container = ContainerType::WEB;
                $gen = new GeneratorFactory(ReactTSInertiaInstaller::$key);
                break;
            case "react-ts-packages":
                $container = ContainerType::WEB;
                $gen = new GeneratorFactory(ReactTsPackagesInstaller::$key);
                break;
            default :
                $this->error("Invalid Installer Factory Key");
                return;
        }

        $gen->make(
            generatedFor: $container ?? ContainerType::API,
            override: $override,
            version: $version
        );

        switch ($plugin) {
            case "auth" :
                CubeLog::add(new CubeWarning("Don't forgot to re-run your users table migration"));
                break;
        }

        $this->handleCommandLogsAndErrors();
    }
}
