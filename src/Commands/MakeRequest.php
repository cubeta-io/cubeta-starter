<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class MakeRequest extends Command
{
    use AssistCommand;

    public $signature = 'create:request
        {name : The name of the model }
        {attributes : columns with data types}?';

    public $description = 'Create a new request';

    /**
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');

        $this->createRequest($modelName, $attributes);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createRequest($modelName, $attributes): void
    {
        $modelName = $this->modelNaming($modelName);
        $requestName = $this->getRequestName($modelName);

        $stubProperties = [
            '{class}' => $modelName,
            '{rules}' => $this->generateCols($attributes),
        ];

        $requestPath = $this->getRequestPath($requestName, $modelName);
        if (file_exists($requestPath)) {
            return;
        }

        new CreateFile(
            $stubProperties,
            $requestPath,
            __DIR__.'/stubs/request.stub'
        );

        $this->formatFile($requestPath);
        $this->line("<info>Created request:</info> {$requestName}");
    }

    private function getRequestName($modelName): string
    {
        return 'StoreUpdate'.$modelName.'Request';
    }

    private function generateCols($attributes): string
    {
        $rules = '';
        foreach ($attributes as $name => $type) {
            if ($name == 'name' || $name == 'first_name' || $name == 'last_name') {
                $rules .= "\t\t\t'$name'        =>      'required|string|min:3|max:255',\n";

                continue;
            }

            if ($name == 'email') {
                $rules .= "\t\t\t'$name'        =>      'required|string|max:255|email',\n";

                continue;
            }

            if ($name == 'password') {
                $rules .= "\t\t\t'$name'        =>      'required|string|max:255|min:6|confirmed',\n";

                continue;
            }

            if ($name == 'phone' || $name == 'phone_number' || $name == 'number') {
                $rules .= "\t\t\t'$name'        =>      'required|string|max:255|min:6',\n";

                continue;
            }

            if (Str::endsWith($name, '_at')) {
                $rules .= "\t\t\t'$name'        =>      'required|date',\n";

                continue;
            }
            if (Str::startsWith($name, 'is_')) {
                $rules .= "\t\t\t'$name'        =>      'required|boolean',\n";

                continue;
            }
            if (Str::endsWith($name, '_id')) {
                $relationModel = str_replace('_id', '', $name);
                $relationModelPluralName = $this->tableNaming($relationModel);
                $rules .= "\t\t\t'$name'        =>      'required|integer|exists:$relationModelPluralName,id',\n";

                continue;
            }
            if ($type == 'file') {
                $rules .= "\t\t\t'$name'=>'nullable|image|mimes:jpeg,png,jpg|max:2048',\n";

                continue;
            }
            $rules .= "\t\t\t'$name'        =>      'required|$type',\n";
        }

        return $rules;
    }

    /**
     * @throws BindingResolutionException
     */
    private function getRequestPath($requestName, $modelName): string
    {
        $path = $this->appPath()."/app/Http/Requests/$modelName/";

        $this->ensureDirectoryExists($path);

        return $path."$requestName".'.php';
    }
}
