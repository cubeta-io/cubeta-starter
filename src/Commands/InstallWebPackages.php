<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Helpers\FileUtils;
use Illuminate\Console\Command;

class InstallWebPackages extends Command
{
    public $description = 'install required npm packages';

    public $signature = 'init-web-packages';

    public function handle(): void
    {
        $this->info('installing laravel-datatables-vite');
        FileUtils::executeCommandInTheBaseDirectory('composer require yajra/laravel-datatables');

        $this->info('installing laravel-datatables-vite');
        FileUtils::executeCommandInTheBaseDirectory('npm i laravel-datatables-vite');
        FileUtils::executeCommandInTheBaseDirectory('npm install --save datatables.net-fixedheader-bs5');
        FileUtils::executeCommandInTheBaseDirectory('npm install --save datatables.net-fixedcolumns-bs5');

        $this->info('installing jquery');
        FileUtils::executeCommandInTheBaseDirectory('npm install jquery');

        $this->info('installing bootstrap 5.2.3');
        FileUtils::executeCommandInTheBaseDirectory('npm install bootstrap@v5.2.3');

        $this->info('installing select2');
        FileUtils::executeCommandInTheBaseDirectory('npm i select2');
        FileUtils::executeCommandInTheBaseDirectory('npm install select2-bootstrap-5-theme');

        $this->info('installing baguettebox');
        FileUtils::executeCommandInTheBaseDirectory('npm i baguettebox.js');

        $this->info('installing tinymce');
        FileUtils::executeCommandInTheBaseDirectory('npm install tinymce');

        $this->info('installing sweetalert2');
        FileUtils::executeCommandInTheBaseDirectory('npm i sweetalert2');

        $this->info('installing sass');
        FileUtils::executeCommandInTheBaseDirectory('npm i sass');
    }
}
