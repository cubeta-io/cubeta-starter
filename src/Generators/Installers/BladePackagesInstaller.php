<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;

class BladePackagesInstaller extends AbstractGenerator
{
    public static string $key = "install-blade-packages";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        FileUtils::executeCommandInTheBaseDirectory('composer require yajra/laravel-datatables');

        FileUtils::executeCommandInTheBaseDirectory("npm install ".
            " vite ".
            " jquery ".
            " select2 ".
            " tinymce ".
            " bootstrap ".
            " sweetalert2 ".
            " baguettebox.js ".
            " bootstrap-icons ".
            " laravel-vite-plugin " .
            " datatables.net-buttons ".
            " select2-bootstrap-5-theme ".
            " datatables.net-select-bs5 ".
            " datatables.net-buttons-bs5 ".
            " datatables.net-fixedheader-bs5 ".
            " datatables.net-fixedcolumns-bs5 "
        );
    }
}
