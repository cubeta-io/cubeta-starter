<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class ReactTSInertiaInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-react";
    public string $type = "installer";

    public function run(bool $override = false): void
    {
        $this->installInertia($override);
        $this->addSetLocalRoute();
        $this->addRouteFile('public', container: ContainerType::WEB, version: $this->version);
        $this->addRouteFile('protected', container: ContainerType::WEB, version: $this->version);
        FileUtils::registerMiddleware(
            "'locale' => AcceptedLanguagesMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            "use App\\Http\\Middleware\\AcceptedLanguagesMiddleware ;"
        );
        FileUtils::registerMiddleware(
            "HandleInertiaRequests::class",
            MiddlewareArrayGroupEnum::WEB,
            "use App\\Http\\Middleware\\HandleInertiaRequests ;"
        );
    }

    private function installInertia(bool $override = false): void
    {
        Settings::make()->setFrontendType(FrontendTypeEnum::REACT_TS);

        Artisan::call('vendor:publish', [
            '--tag'   => 'react-ts',
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

        CubeLog::add(new SuccessMessage("Your Frontend Stack Has Been Set To " . FrontendTypeEnum::REACT_TS->value));
    }
}
