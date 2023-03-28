<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;

class MakeSeeder extends Command
{
    use AssistCommand;

    public $signature = 'create:seeder
        {name : The name of the model }';

    public $description = 'Create a new seeder';

    /**
     * Handle the command
     *
     * @return void
     */
    public function handle()
    {
        $modelName = $this->argument("name");

        $this->createSeeder($modelName);
    }

    private function createSeeder($modelName){

        $seederName = $this->getSeederName($modelName);

        $stubProperties = [
            "{class}"               => $modelName ,
        ];

        new CreateFile(
            $stubProperties,
            $this->getSeederPath($seederName),
            __DIR__ . "/stubs/seeder.stub"
        );
        $this->line("<info>Created seeder:</info> {$seederName}");
    }

    private function getSeederName($modelName){
        return $modelName."Seeder";;
    }

    private function getSeederPath($seederName)
    {
        return $this->appDatabasePath() . "/seeders" .
            "/$seederName" . ".php";
    }
}
