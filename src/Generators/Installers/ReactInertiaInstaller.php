<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Illuminate\Support\Facades\Artisan;

class ReactInertiaInstaller extends AbstractGenerator
{
    public static string $key = "install-react";
    public string $type = "installer";

    public function run(bool $override = false): void
    {
        $this->installInertia();
    }

    private function installInertia(): void
    {
        // install inertia
        FileUtils::executeCommandInTheBaseDirectory("composer require inertiajs/inertia-laravel");
        //install js packages
        FileUtils::executeCommandInTheBaseDirectory('npm install @inertiajs/react tailwindcss @tailwindcss/forms @types/node @types/react @types/react-dom @vitejs/plugin-react postcss react react-dom typescript @tinymce/tinymce-react @vitejs/plugin-react-refresh');

        // adding the app layout blade file
        $this->generateFileFromStub(
            stubProperties: [],
            path: resource_path('/views/app.blade.php'),
            otherStubsPath: __DIR__ . '/../../stubs/Inertia/views/app-view.stub'
        );

        // adding inertia middleware
        $this->generateFileFromStub(
            stubProperties: [],
            path: app_path('/Http/Middleware/HandleInertiaRequests.php'),
            otherStubsPath: __DIR__ . '/../../stubs/Inertia/HandleInertiaRequestsMiddleware.stub'
        );

        Artisan::call('vendor:publish', [
            '--tag' => 'inertia-react',
            '--force' => true
        ]);
    }
}
