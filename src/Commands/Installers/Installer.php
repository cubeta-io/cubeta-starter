<?php

namespace Cubeta\CubetaStarter\Commands\Installers;

use Cubeta\CubetaStarter\Commands\BaseCommand;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\ValidationTypeEnum;
use Cubeta\CubetaStarter\Generators\GeneratorFactory;
use Cubeta\CubetaStarter\Generators\Installers\ApiInstaller;
use Cubeta\CubetaStarter\Generators\Installers\AuthInstaller;
use Cubeta\CubetaStarter\Generators\Installers\BladePackagesInstaller;
use Cubeta\CubetaStarter\Generators\Installers\PermissionsInstaller;
use Cubeta\CubetaStarter\Generators\Installers\ReactTSInertiaInstaller;
use Cubeta\CubetaStarter\Generators\Installers\ReactTsPackagesInstaller;
use Cubeta\CubetaStarter\Generators\Installers\WebInstaller;
use Cubeta\CubetaStarter\Settings\Settings;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class Installer extends BaseCommand
{
    protected $description = 'Install a Cubeta Starter stack/plugin (api, web, auth, permissions, react-ts, ...) into the host app';

    protected $signature = 'cubeta:install
        {name? : plugin name: api, web, web-packages, auth, permissions, react-ts or react-ts-packages }
        {version=v1 : the api version prefix used for routes, e.g. v1 }
        {container? : api, web or both - only used by the "auth" plugin }
        {--validation= : FormRequest, DTO or Both - only used by the api, web and react-ts plugins }
        {--force : overwrite existing files instead of skipping/prompting }';

    public function getHelp(): string
    {
        return <<<HELP
          One-time stack setup for the host Laravel app. Run once per plugin before generating
          model files that depend on it:

            api               base api scaffolding (routes, base controller, exception handling, ...)
            web               base blade scaffolding
            web-packages      front-end packages/build tooling for the blade stack
            auth              authentication controllers/endpoints for the chosen container
            permissions       installs spatie/laravel-permission and role scaffolding
            react-ts          Inertia + React + TypeScript scaffolding
            react-ts-packages front-end packages/build tooling for the react-ts stack

          Examples:
            php artisan cubeta:install api
            php artisan cubeta:install api v1 --validation=DTO --force --no-interaction
            php artisan cubeta:install auth v1 api --force --no-interaction
          HELP;
    }

    public function handle(): void
    {
        $plugin = $this->argument('name');
        $plugins = ['api', 'web', 'web-packages', 'auth', 'permissions', 'react-ts', 'react-ts-packages'];

        if (!$plugin) {
            $plugin = select("What you want to install ?", $plugins);
        }

        $version = $this->argument('version');

        if (!in_array($plugin, $plugins)) {
            error("Invalid Input");
            warning("Installed Plugin Should Be One Of The Following : " . collect($plugins)->toJson());
            return;
        }

        if (in_array($plugin, ['api', 'web', 'react-ts'])) {
            if ($validation = $this->option('validation')) {
                Settings::make()->setValidationType(ValidationTypeEnum::tryFrom($validation) ?? ValidationTypeEnum::FORM_REQUEST);
            } else {
                $this->askForValidationType();
            }
        }

        $override = $this->askForOverride();

        switch ($plugin) {
            case "api" :
                $gen = new GeneratorFactory(ApiInstaller::$key);
                break;
            case "web" :
                $container = ContainerType::WEB;
                $gen = new GeneratorFactory(WebInstaller::$key);
                break;
            case "web-packages" :
                $container = ContainerType::WEB;
                $gen = new GeneratorFactory(BladePackagesInstaller::$key);
                break;
            case "permissions" :
                $gen = new GeneratorFactory(PermissionsInstaller::$key);
                break;
            case "auth" :
                $container = $this->argument('container') ?? $this->askForContainer();
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
                error("Invalid Installer Factory Key");
                return;
        }

        $gen->make(
            generatedFor: $container ?? ContainerType::API,
            override: $override,
            version: $version
        );
    }
}
