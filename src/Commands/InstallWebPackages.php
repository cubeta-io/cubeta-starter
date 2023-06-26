<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;

class InstallWebPackages extends Command
{
    use AssistCommand;

    public $description = 'install required npm packages';

    public $signature = 'init-web-packages';

    public function handle(): void
    {
        $this->info('installing laravel-datatables-vite');
        $this->executeCommandInTheBaseDirectory('composer require yajra/laravel-datatables');

        $this->info('installing laravel-datatables-vite');
        $this->executeCommandInTheBaseDirectory('npm i laravel-datatables-vite');
        $this->executeCommandInTheBaseDirectory('npm i datatables.net-buttons');

        $this->info('installing jquery');
        $this->executeCommandInTheBaseDirectory('npm install jquery');

        $this->info('installing bootstrap 5.2.3');
        $this->executeCommandInTheBaseDirectory('npm install bootstrap@v5.2.3');

        $this->info('installing select2');
        $this->executeCommandInTheBaseDirectory('npm i select2');
        $this->executeCommandInTheBaseDirectory('npm install select2-bootstrap-5-theme');

        $this->info('installing baguettebox');
        $this->executeCommandInTheBaseDirectory('npm i baguettebox.js');

        $this->info('installing tinymce');
        $this->executeCommandInTheBaseDirectory('npm install tinymce');

        $this->info('installing sweetalert2');
        $this->executeCommandInTheBaseDirectory('npm i sweetalert2');

        $this->info('installing sass');
        $this->executeCommandInTheBaseDirectory('npm i sass');
    }
}
