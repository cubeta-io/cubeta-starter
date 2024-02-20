<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

trait RouteFileTrait
{
    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addRouteFile(string $role, $container = null): void
    {
        $role = Str::singular(Str::lower($role));


        if ($role == 'none') {
            $role = $container;
        }

        if ($role == ContainerType::WEB and $container == ContainerType::WEB) {
            $role = 'dashboard';
        }

        $routeFile = "v1/{$container}/{$role}.php";
        $routeFileDirectory = base_path("routes/{$routeFile}");

        ensureDirectoryExists(dirname($routeFileDirectory));

        generateFileFromStub(
            ['{route}' => '//add-your-routes-here'],
            $routeFileDirectory,
            __DIR__ . '/../Commands/stubs/api.stub'
        );

        $this->addRouteFileToServiceProvider($routeFile, $container);
    }

    /**
     * add Route File Binding to the RouteServiceProvider
     */
    public function addRouteFileToServiceProvider(string $routeFilePath, string $container = ContainerType::API): void
    {
        $routeServiceProvider = app_path('Providers/RouteServiceProvider.php');

        if ($container == ContainerType::API) {
            $lineToAdd = "\t\t Route::middleware('api')\n" .
                "\t\t\t->prefix('api')\n" .
                "\t\t\t->group(base_path('routes/{$routeFilePath}'));\n";
        }

        if ($container == ContainerType::WEB) {
            $lineToAdd = "\t\t Route::middleware('web')\n" .
                "\t\t\t->group(base_path('routes/{$routeFilePath}'));\n";
        }

        // Read the contents of the file
        $fileContent = file_get_contents($routeServiceProvider);

        // Check if the line to add already exists in the file
        if (!str_contains($fileContent, $lineToAdd)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0{$lineToAdd}";

            $fileContent = preg_replace($pattern, $replacement, $fileContent, 1);
            // Write the modified contents back to the file
            file_put_contents($routeServiceProvider, $fileContent);
        }

        $this->formatFile($routeServiceProvider);
    }
}
