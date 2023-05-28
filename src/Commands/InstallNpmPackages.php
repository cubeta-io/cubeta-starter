<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;

class InstallNpmPackages extends Command
{
    use AssistCommand;

    public $signature = 'install-npm-packages';

    public $description = 'install required npm packages';

    public function handle(): void
    {
        $this->info("installing laravel-datatables-vite");
        $this->executeCommandInTheBaseDirectory("npm i laravel-datatables-vite --save-dev");
        $this->info("installing jquery");
        $this->executeCommandInTheBaseDirectory("npm install jquery --save-dev");
        $this->info("installing bootstrap 5.2.3");
        $this->executeCommandInTheBaseDirectory("npm install bootstrap@v5.2.3 --save-dev");
        $this->info("installing select2");
        $this->executeCommandInTheBaseDirectory("npm i select2 --save-dev");
        $this->executeCommandInTheBaseDirectory("npm install select2-bootstrap-5-theme");
        $this->info("installing baguettebox");
        $this->executeCommandInTheBaseDirectory("npm i baguettebox.js --save-dev");
        $this->info("installing trumbowyg");
        $this->executeCommandInTheBaseDirectory("npm i trumbowyg --save-dev");
        $this->info("installing sweetalert2");
        $this->executeCommandInTheBaseDirectory("npm i sweetalert2 --save-dev");
    }
}
