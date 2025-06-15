<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Exceptions\ExceptionHandlerStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Routes\RoutesFileStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Tests\MainTestCaseStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Traits\TestHelpersStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ApiInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-api";

    public string $type = 'installer';

    public function run(): void
    {
        PackageManager::composerInstall("maatwebsite/excel");

        $this->publishBaseRepository();
        $this->publishBaseService();
        $this->publishMakableTrait();
        $this->publishHasMediaTrait();

        Artisan::call("vendor:publish", ['--force' => $this->override, "--tag" => "cubeta-starter-api"]);
        CubeLog::add(Artisan::output());

        $this->publishTestTools();

        $this->addApiRouteFile();
        $this->addAndRegisterAuthenticateMiddleware($this->override);
        $this->addRouteFile('public', version: $this->version, override: $this->override);
        $this->addRouteFile('protected', version: $this->version, middlewares: ["authenticated:api"], override: $this->override);
        $this->registerExceptionsHandler();

        FileUtils::registerMiddleware(
            "'locale' => AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString("App\\Http\\Middleware\\AcceptedLanguagesMiddleware")
        );

        $this->registerHelpersFile();

        Settings::make()->setInstalledApi();
    }

    private function addApiRouteFile(): void
    {
        $apiFilePath = CubePath::make('/routes/api.php');

        RoutesFileStubBuilder::make()
            ->generate($apiFilePath, $this->override);

        $bootstrapPath = CubePath::make('/bootstrap/app.php');
        if (!$bootstrapPath->exist()) {
            CubeLog::notFound($bootstrapPath->fullPath, "Installing api tools");
            return;
        }

        $bootstrapContent = $bootstrapPath->getContent();
        $newRouteFile = "api: __DIR__.'/../routes/api.php'";
        $pattern = '/->\s*withRouting\s*\(\s*(.*?)\s*\)\s*/s';
        if (!preg_match($pattern, $bootstrapContent, $matches)) {
            CubeLog::failedAppending($newRouteFile, $bootstrapPath->fullPath, "Installing api tools");
            return;
        }

        if (!isset($matches[1])) {
            CubeLog::failedAppending($newRouteFile, $bootstrapPath->fullPath, "Installing api tools");
            return;
        }

        if (FileUtils::contentExistsInString($matches[1], $newRouteFile)) {
            CubeLog::contentAlreadyExists($newRouteFile, $bootstrapPath->fullPath, "Installing api tools");
            return;
        }

        $registered = $matches[1];
        if (preg_match('/\s*web\s*:\s*(.*?)\s*,\s*/s', $registered, $innerMatches)) {
            $registered = str_replace($innerMatches[0], "$innerMatches[0]\n$newRouteFile,\n", $registered);
        } else {
            $registered = "$newRouteFile,\n$registered";
        }

        $registered = FileUtils::fixArrayOrObjectCommas($registered);
        $bootstrapContent = str_replace($matches[1], $registered, $bootstrapContent);
        $bootstrapPath->putContent($bootstrapContent);
        $bootstrapPath->format();
        CubeLog::contentAppended($newRouteFile, $bootstrapPath->fullPath);
    }

    public function registerExceptionsHandler(): bool
    {
        $handlerPath = CubePath::make('app/Exceptions/Handler.php');

        if (!ExceptionHandlerStubBuilder::make()->generate($handlerPath, $this->override)) {
            return false;
        }

        $bootstrapPath = CubePath::make("/bootstrap/app.php");

        if (!$bootstrapPath->exist()) {
            CubeLog::notFound($bootstrapPath->fullPath, "Registering Exception Handler");
            return false;
        }

        $bootstrapContent = $bootstrapPath->getContent();
        $handler = '
        if(!request()->acceptsHtml()){
            $exceptions->render(function (Exception $exception, Request $request) {
                $handler = new Handler;
                return $handler->handleException($request, $exception);
            });
        }';


        $pattern = '/->\s*withExceptions\s*\((.*?)\)->(.*?)create\(\)\s*;/s';
        if (!preg_match($pattern, $bootstrapContent, $matches)) {
            CubeLog::failedAppending($handler, $bootstrapPath->fullPath, "Registering Exception Handler");
            return false;
        }

        if (empty($matches[1])) {
            CubeLog::failedAppending($handler, $bootstrapPath->fullPath, "Registering Exception Handler");
            return false;
        }

        $exceptionsFunction = $matches[1];

        if (FileUtils::contentExistsInString($exceptionsFunction, $handler)) {
            CubeLog::contentAlreadyExists($handler, $bootstrapPath->fullPath, "Registering Exception Handler");
            return false;
        }

        $newExceptionsFunction = preg_replace(
            '/function\s*\(\s*Exceptions\s*\$exceptions\s*\)(.*?)\{(.*?)}/s',
            "function (Exceptions \$exceptions)$1{\n$2\n$handler}",
            $exceptionsFunction
        );

        $bootstrapContent = str_replace($matches[1], $newExceptionsFunction, $bootstrapContent);
        $bootstrapPath->putContent($bootstrapContent);
        CubeLog::contentAppended($handler, $bootstrapPath->fullPath);
        $bootstrapPath->format();


        FileUtils::addImportStatement(new PhpImportString("App\Exceptions\Handler"), $bootstrapPath);
        FileUtils::addImportStatement(new PhpImportString(Exceptions::class), $bootstrapPath);
        FileUtils::addImportStatement(new PhpImportString(Request::class), $bootstrapPath);

        return true;
    }

    private function publishTestTools(): void
    {
        $testHelpersPath = CubePath::make(config('cubeta-starter.trait_path') . '/TestHelpers.php');
        TestHelpersStubBuilder::make()
            ->traitsNamespace(config('cubeta-starter.trait_namespace'))
            ->modelsNamespace(config('cubeta-starter.model_namespace'))
            ->resourcesNamespace(config('cubeta-starter.resource_namespace'))
            ->generate($testHelpersPath, $this->override);

        $mainTestCasePath = CubePath::make('/tests/Contracts/MainTestCase.php');
        MainTestCaseStubBuilder::make()
            ->modelsNamespace(config('cubeta-starter.model_namespace'))
            ->traitsNamespace(config('cubeta-starter.trait_namespace'))
            ->generate($mainTestCasePath, $this->override);
    }
}
