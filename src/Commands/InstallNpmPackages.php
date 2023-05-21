<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;

class MakeSeeder extends Command
{
    use AssistCommand;

    public $signature = 'install npm packages';

    public $description = 'install required npm packages';

    public function handle(): void
    {
        $this->executeCommandInTheBaseDirectory("npm i laravel-datatables-vite --save-dev");
        $this->executeCommandInTheBaseDirectory("npm install jquery --save-dev");
        $this->executeCommandInTheBaseDirectory("npm install bootstrap@v5.2.3 --save-dev");
        $this->executeCommandInTheBaseDirectory("npm i select2 --save-dev");
        $this->executeCommandInTheBaseDirectory("npm i baguettebox.js --save-dev");
        $this->executeCommandInTheBaseDirectory("npm i trumbowyg --save-dev");
        $this->executeCommandInTheBaseDirectory("npm i sweetalert2 --save-dev");
    }
}
