<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Illuminate\Support\Facades\Artisan;

class BladePackagesInstaller extends AbstractGenerator
{
    public static string $key = "install-blade-packages";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        FileUtils::executeCommandInTheBaseDirectory("composer require " .
            " yajra/laravel-datatables " .
            " maatwebsite/excel"
        );

        Artisan::call('vendor:publish', [
            '--tage' => 'datatables'
        ]);

        FileUtils::executeCommandInTheBaseDirectory("npm install " .
            // Facilities
            " vite " .
            " laravel-vite-plugin" .
            " jquery " .
            " sass " .
            " select2 " .
            " bootstrap " .
            " bootstrap-icons " .
            " laravel-vite-plugin " .
            // UI tools
            " tinymce " .
            // select 2
            " sweetalert2 " .
            " select2-bootstrap-5-theme " .
            " baguettebox.js " .
            // Datatables
            " datatables.net-bs5 " .
            " datatables.net-buttons-bs5 " .
            " datatables.net-fixedcolumns-bs5 " .
            " datatables.net-fixedheader-bs5 " .
            " datatables.net-select-bs5 "
        );

        Settings::make()->setInstalledWeb();
        Settings::make()->setFrontendType(FrontendTypeEnum::BLADE);
    }
}
