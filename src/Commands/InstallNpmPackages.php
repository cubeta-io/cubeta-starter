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
        $this->executeCommandInTheBaseDirectory("npm i laravel-datatables-vite --save-dev");
        $this->line("installed laravel-datatables-vite");
        $this->executeCommandInTheBaseDirectory("npm install jquery --save-dev");
        $this->line("installed jquery");
        $this->executeCommandInTheBaseDirectory("npm install bootstrap@v5.2.3 --save-dev");
        $this->line("installed bootstrap 5.2.3");
        $this->executeCommandInTheBaseDirectory("npm i select2 --save-dev");
        $this->line("installed select2");
        $this->executeCommandInTheBaseDirectory("npm i baguettebox.js --save-dev");
        $this->line("installed baguettebox");
        $this->executeCommandInTheBaseDirectory("npm i trumbowyg --save-dev");
        $this->line("installed trumbowyg");
        $this->executeCommandInTheBaseDirectory("npm i sweetalert2 --save-dev");
        $this->line("installed sweetalert2");
    }
}
