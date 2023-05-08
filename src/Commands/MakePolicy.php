<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePolicy extends Command
{
    use AssistCommand;

    public $signature = 'create:policy
        {name : The name of the Policy }
        {actor : The actor of the endpoint of this model }';

    public $description = 'Create a new Policy class';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $actor = $this->argument('actor');

        if (isset($actor) && $actor != 'none') {
            $this->createPolicy($name);
        }
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createPolicy($modelName): void
    {
        $modelName = Str::singular(ucfirst(Str::studly($modelName)));

        $policyName = $modelName.'Policy';

        $stubProperties = [
            '{modelName}' => $modelName,
        ];

        $policyPath = base_path().'/app/Policies/'.$policyName.'.php';
        if (file_exists($policyPath)) {
            return;
        }

        // check folder exist
        $folder = base_path().'/app/Policies/';
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }

        // create file
        new CreateFile(
            $stubProperties,
            $folder.$policyName.'.php',
            __DIR__.'/stubs/policy.stub'
        );

        $this->line("<info>Created Repository:</info> $policyName");
    }
}
