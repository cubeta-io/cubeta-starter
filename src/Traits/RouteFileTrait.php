<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait RouteFileTrait
{
    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addRouteFile($role, $container = null): void
    {
        $role = Str::singular(Str::lower($role));

        $routeFile = "$container/$role.php";
        $routeFileDirectory = base_path("routes/$routeFile.php");

        ! (File::makeDirectory(dirname($routeFileDirectory), 0777, true, true)) ??
        $this->error('Failed To Create Your Route Specified Directory');

        generateFileFromStub(
            ['{route}' => '//add-your-routes-here'],
            $routeFileDirectory,
            __DIR__.'/../Commands/stubs/api.stub'
        );

        $this->addRouteFileToServiceProvider($routeFile);
    }

    /**
     * add Route File Binding to the RouteServiceProvider
     */
    public function addRouteFileToServiceProvider(string $routeFilePath): void
    {
        $routeServiceProvider = app_path('Providers/RouteServiceProvider.php');
        $line_to_add = "\t\t Route::middleware('api')\n".
            "\t\t\t->prefix('api')\n".
            "\t\t\t->group(base_path('routes/$routeFilePath'));\n";

        // Read the contents of the file
        $file_contents = file_get_contents($routeServiceProvider);

        // Check if the line to add already exists in the file
        if (! str_contains($file_contents, $line_to_add)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0$line_to_add";

            $file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
            // Write the modified contents back to the file
            file_put_contents($routeServiceProvider, $file_contents);
        }

        $this->formatFile($routeServiceProvider);
    }

    /**
     * call the addRouteFile method on the appropriate container
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addAppropriateRouteFile($container, $role): void
    {
        if ($container == 'both') {
            $this->addRouteFile($role, 'web');
            $this->addRouteFile($role, 'api');
        } else {
            $this->addRouteFile($role, $container);
        }
    }
}