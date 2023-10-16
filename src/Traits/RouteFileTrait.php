<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

trait RouteFileTrait
{
    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addRouteFile($role, $container = null): void
    {
        $role = Str::singular(Str::lower($role));

        $routeFile = "v1/{$container}/{$role}.php";
        $routeFileDirectory = base_path("routes/{$routeFile}");

        !(File::makeDirectory(dirname($routeFileDirectory), 0777, true, true)) ??
        $this->error('Failed To Create Your Route Specified Directory');

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
    public function addRouteFileToServiceProvider(string $routeFilePath, string $container = 'api'): void
    {
        $routeServiceProvider = app_path('Providers/RouteServiceProvider.php');

        if ($container == 'api') {
            $line_to_add = "\t\t Route::middleware('api')\n" .
                "\t\t\t->prefix('api')\n" .
                "\t\t\t->group(base_path('routes/{$routeFilePath}'));\n";
        }

        if ($container == 'web') {
            $line_to_add = "\t\t Route::middleware('web')\n" .
                "\t\t\t->group(base_path('routes/{$routeFilePath}'));\n";
        }

        // Read the contents of the file
        $file_contents = file_get_contents($routeServiceProvider);

        // Check if the line to add already exists in the file
        if (!str_contains($file_contents, $line_to_add)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0{$line_to_add}";

            $file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
            // Write the modified contents back to the file
            file_put_contents($routeServiceProvider, $file_contents);
        }

        $this->formatFile($routeServiceProvider);
    }
}
