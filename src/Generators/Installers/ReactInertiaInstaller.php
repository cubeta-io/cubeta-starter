<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Illuminate\Support\Facades\Artisan;

class ReactInertiaInstaller extends AbstractGenerator
{
    public static string $key = "install-react";
    public string $type = "installer";

    public function run(bool $override = false): void
    {
        $this->installInertia($override);
        $this->preparePackageJson();
    }

    private function installInertia(bool $override = false): void
    {
        // install inertia
        FileUtils::executeCommandInTheBaseDirectory("composer require inertiajs/inertia-laravel");
        //install ziggy route handler
        FileUtils::executeCommandInTheBaseDirectory("composer require tightenco/ziggy");
        //generate route handler type system
        FileUtils::executeCommandInTheBaseDirectory("php artisan ziggy:generate --types");
        //install js packages
        FileUtils::executeCommandInTheBaseDirectory('npm install @inertiajs/react tailwindcss @tailwindcss/forms @types/node @types/react @types/react-dom @vitejs/plugin-react postcss react react-dom typescript @tinymce/tinymce-react @vitejs/plugin-react-refresh autoprefixer sweetalert2 sweetalert2-react-content react-toastify');

        // adding the app layout blade file
        $this->generateFileFromStub(
            stubProperties: [],
            path: resource_path('/views/app.blade.php'),
            override: $override,
            otherStubsPath: __DIR__ . '/../../stubs/Inertia/views/app-view.stub'
        );

        // adding inertia middleware
        $this->generateFileFromStub(
            stubProperties: [],
            path: app_path('/Http/Middleware/HandleInertiaRequests.php'),
            override: $override,
            otherStubsPath: __DIR__ . '/../../stubs/Inertia/HandleInertiaRequestsMiddleware.stub'
        );

        Artisan::call('vendor:publish', [
            '--tag' => 'inertia-react',
            '--force' => $override,
        ]);
    }

    public function preparePackageJson(): void
    {
        $packageJsonPath = CubePath::make('/package.json');
        $jsonArray = json_decode($packageJsonPath->getContent(), true);

        if (isset($jsonArray['type']) && $jsonArray['type'] == "module") {
            return;
        } else {
            $jsonArray['type'] = "module";
            $packageJsonPath->putContent(json_encode($jsonArray, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));
            CubeLog::add(new ContentAppended('"type":"module"', $packageJsonPath->fullPath));
        }
    }
}
