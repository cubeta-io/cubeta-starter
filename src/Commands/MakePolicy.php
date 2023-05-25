<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakePolicy extends Command
{
    use AssistCommand;

    public $signature = 'create:policy
        {name : The name of the Policy }';

    public $description = 'Create a new Policy class';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $this->createPolicy($name);
    }

    /**
     * @param $modelName
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createPolicy($modelName): void
    {
        $modelName = modelNaming($modelName);

        $policyName = $modelName . 'Policy';

        $stubProperties = [
            "{namespace}" => config('repository.policy_namespace'),
            '{modelName}' => $modelName,
        ];

        $policyPath = $this->getPolicyPath($policyName);
        if (file_exists($policyPath)) {
            $this->line("<info>$policyName Already Exist</info>");
            return;
        }

        $this->ensureDirectoryExists($policyPath);

        // create file
        new CreateFile(
            $stubProperties,
            $policyPath,
            __DIR__ . '/stubs/policy.stub'
        );

        $this->formatFile($policyPath);
        $this->line("<info>Created Policy:</info> $policyName");
    }

    /**
     * @param string $policyName
     * @return string
     * @throws BindingResolutionException
     */
    public function getPolicyPath(string $policyName): string
    {
        $directory = base_path(config('repository.policy_path'));
        $this->ensureDirectoryExists($directory);
        return "$directory/$policyName.php";
    }
}
