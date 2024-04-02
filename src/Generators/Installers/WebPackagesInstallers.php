<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;

class WebPackagesInstallers extends AbstractGenerator
{
    public static string $key = "install-web-packages";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        FileUtils::executeCommandInTheBaseDirectory('composer require yajra/laravel-datatables');
        FileUtils::executeCommandInTheBaseDirectory('npm i laravel-datatables-vite');
        FileUtils::executeCommandInTheBaseDirectory('npm install --save datatables.net-fixedheader-bs5');
        FileUtils::executeCommandInTheBaseDirectory('npm install --save datatables.net-fixedcolumns-bs5');
        FileUtils::executeCommandInTheBaseDirectory('npm install jquery');
        FileUtils::executeCommandInTheBaseDirectory('npm install bootstrap@v5.2.3');
        FileUtils::executeCommandInTheBaseDirectory('npm i select2');
        FileUtils::executeCommandInTheBaseDirectory('npm install select2-bootstrap-5-theme');
        FileUtils::executeCommandInTheBaseDirectory('npm i baguettebox.js');
        FileUtils::executeCommandInTheBaseDirectory('npm install tinymce');
        FileUtils::executeCommandInTheBaseDirectory('npm i sweetalert2');
        FileUtils::executeCommandInTheBaseDirectory('npm i sass');
    }
}
