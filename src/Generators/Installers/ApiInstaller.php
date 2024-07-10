<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessGenerating;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;
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

        $this->addApiRouteFile();
        $this->addRouteFile('public', version: $this->version);
        $this->addRouteFile('protected', version: $this->version);
        $this->registerExceptoptionHandler($override);
        FileUtils::registerMiddleware(
            "'locale' => App\\Http\\Middleware\\AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS
        );
    }

    private function addApiRouteFile(): void
    {
        $apiFilePath = CubePath::make('/routes/api.php');

        if ($apiFilePath->exist()) {
            $apiFilePath->logAlreadyExist("Installing api tools");
            return;
        }

        try {
            FileUtils::generateFileFromStub(
                ['{route}' => '//add-your-routes-here'],
                $apiFilePath->fullPath,
                __DIR__ . '/../../stubs/api.stub'
            );
            CubeLog::add(new SuccessGenerating($apiFilePath->fileName, $apiFilePath->fullPath, "Installing api tools"));
        } catch (Exception $e) {
            CubeLog::add($e);
            return;
        }

        $bootstrapPath = CubePath::make('/bootstrap/app.php');
        if (!$bootstrapPath->exist()) {
            CubeLog::add(new NotFound($bootstrapPath->fullPath, "Installing api tools"));
            return;
        }
        $bootstrapContent = $bootstrapPath->getContent();
        $newRouteFile = "api: __DIR__.'/../routes/api.php'";
        $pattern = '/->\s*withRouting\s*\(\s*(.*?)\s*\)\s*/s';
        if (preg_match($pattern, $bootstrapContent, $matches)) {
            if (isset($matches[1])) {
                if (FileUtils::contentExistsInString($matches[1], $newRouteFile)) {
                    CubeLog::add(new ContentAlreadyExist($newRouteFile, $bootstrapPath->fullPath, "Installing api tools"));
                    return;
                }
                $registered = $matches[1];
                $registered .= ",\n $newRouteFile,\n";
                $registered = FileUtils::fixArrayOrObjectCommas($registered);
                $bootstrapContent = str_replace($matches[1], $registered, $bootstrapContent);
                $bootstrapPath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($newRouteFile, $bootstrapPath->fullPath));
                $bootstrapPath->format();
            } else {
                CubeLog::add(new FailedAppendContent($newRouteFile, $bootstrapPath->fullPath, "Installing api tools"));
                return;
            }
        }

        CubeLog::add(new ContentAppended($newRouteFile, $bootstrapPath->fullPath));
    }

    public function registerExceptionsHandler()
    {
        $bootstrapPath = CubePath::make("/bootstrap/app.php");

        if (!$bootstrapPath->exist()) {
            CubeLog::add(new NotFound($bootstrapPath->fullPath, "Registering Exception Handler"));
            return false;
        }
        FileUtils::addImportStatement("use App\Exceptions\Handler;", $bootstrapPath);
        FileUtils::addImportStatement("use Illuminate\Foundation\Configuration\Exceptions;", $bootstrapPath);
        FileUtils::addImportStatement("use Illuminate\Http\Request;", $bootstrapPath);

        $bootstrapContent = $bootstrapPath->getContent();
        $handler = '
        if(!request()->acceptsHtml()){
            $exceptions->render(function (Exception $exception, Request $request) {
                $handler = new Handler();
                return $handler->handleException($request, $exception);
            });
        }';

        $pattern = '/->\s*withExceptions\s*\(\s*function\s*\(\s*Exceptions\s*\$exceptions\s*\)\s*\{\s*(.*?)\s*}\s*\)/';
        if (preg_match($pattern, $bootstrapContent, $matches)) {
            if (isset($matches[1])) {
                $handlers = $matches[1];
                if (FileUtils::contentExistsInString($handlers, $handler)) {
                    CubeLog::add(new ContentAlreadyExist($handler, $bootstrapPath->fullPath, "Registering Exception Handler"));
                    return false;
                }
                $handlers .= "\n$handler\n";
                $bootstrapContent = preg_replace_callback($pattern, function () use ($handlers) {
                    return "->withExceptions(function (Exceptions \$exceptions) {\n$handlers\n})";
                }, $bootstrapContent);
                $bootstrapPath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($handler, $bootstrapPath->fullPath));
                $bootstrapPath->format();
                return true;
            } else {
                CubeLog::add(new FailedAppendContent($handler, $bootstrapPath->fullPath, "Registering Exception Handler"));
                return false;
            }
        }

        CubeLog::add(new FailedAppendContent($handler, $bootstrapPath->fullPath, "Registering Exception Handler"));
        return false;
    }

    /**
     * @param bool $override
     * @return void
     */
    private function registerExceptoptionHandler(bool $override): void
    {
        try {
            FileUtils::generateFileFromStub(
                [],
                base_path('app/Exceptions/Handler.php'),
                __DIR__ . '/../../stubs/handler.stub',
                $override
            );
            CubeLog::add(new SuccessGenerating("Handler.php", base_path('app/Exceptions/Handler.php'), "Installing api tools"));
            $this->registerExceptionsHandler();
        } catch (Exception $exception) {
            CubeLog::add($exception);
        }
    }
}
