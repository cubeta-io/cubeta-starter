<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Illuminate\Support\Facades\Artisan;

class ReactTSInertiaInstaller extends AbstractGenerator
{
    public static string $key = "install-react";
    public string $type = "installer";

    public function run(bool $override = false): void
    {
        $this->installInertia($override);
    }

    private function installInertia(bool $override = false): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'react-ts',
            '--force' => $override,
        ]);

        CubeLog::add(Artisan::output());

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

        Settings::make()->setFrontendType(FrontendTypeEnum::REACT_TS);
        CubeLog::add(new SuccessMessage("Your Frontend Stack Has Been Set To " . FrontendTypeEnum::REACT_TS->value));
    }
}
