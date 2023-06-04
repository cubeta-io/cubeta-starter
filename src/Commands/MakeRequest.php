<?php

namespace Cubeta\CubetaStarter\Commands;

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
        {attributes? : columns with data types}';

    public $description = 'Create a new request';

    /**
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];

        $this->createRequest($modelName, $attributes);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createRequest($modelName, array $attributes = []): void
    {
        $modelName = modelNaming($modelName);
        $requestName = $this->getRequestName($modelName);

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.request_namespace')."\\$modelName",
            '{class}' => $modelName,
            '{rules}' => $this->generateCols($attributes),
        ];

        $requestPath = $this->getRequestPath($requestName, $modelName);
        if (file_exists($requestPath)) {
            $this->error("$requestName Already Exist");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $requestPath,
            __DIR__.'/stubs/request.stub'
        );

        if (in_array('translatable', $attributes)) {
            addImportStatement("use Cubeta\CubetaStarter\Rules\LanguageShape; \n", $requestPath);
        }

        $this->formatFile($requestPath);
        $this->info("Created request: $requestName");
    }

    private function getRequestName($modelName): string
    {
        return 'StoreUpdate'.$modelName.'Request';
    }

    private function generateCols(array $attributes = []): string
    {
        $rules = '';
        foreach ($attributes as $name => $type) {

            if ($type == 'translatable') {
                $rules .= "\t\t\t'$name'=>['required', 'json', new LanguageShape] , \n";

                continue;
            }
            if ($name == 'name' || $name == 'first_name' || $name == 'last_name') {
                $rules .= "\t\t\t'$name'=>'required|string|min:3|max:255',\n";

                continue;
            }

            if ($name == 'email') {
                $rules .= "\t\t\t'$name'=>'required|string|max:255|email',\n";

                continue;
            }

            if ($name == 'password') {
                $rules .= "\t\t\t'$name'=>'required|string|max:255|min:6|confirmed',\n";

                continue;
            }

            if ($name == 'phone' || $name == 'phone_number' || $name == 'number') {
                $rules .= "\t\t\t'$name'=>'required|string|max:255|min:6',\n";

                continue;
            }

            if (Str::endsWith($name, '_at') || in_array($type, ['date', 'time', 'dateTime', 'timestamp'])) {
                $rules .= "\t\t\t'$name'=>'required|date',\n";

                continue;
            }

            if (Str::startsWith($name, 'is_') || $type == 'boolean') {
                $rules .= "\t\t\t'$name'=>'required|boolean',\n";

                continue;
            }

            if (Str::endsWith($name, '_id')) {
                $relationModel = str_replace('_id', '', $name);
                $relationModelPluralName = tableNaming($relationModel);
                $rules .= "\t\t\t'$name'=>'required|numeric|exists:$relationModelPluralName,id',\n";

                continue;
            }

            if ($type == 'file') {
                $rules .= "\t\t\t'$name'=>'nullable|image|mimes:jpeg,png,jpg|max:2048',\n";

                continue;
            }

            if ($type == 'text') {
                $rules .= "\t\t\t'$name'=>'nullable|string',\n";

                continue;
            }

            if (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {
                $rules .= "\t\t\t'$name'=>'required|numeric',\n";

                continue;
            }

            $rules .= "\t\t\t'$name'=>'required|$type',\n";
        }

        return $rules;
    }

    private function getRequestPath($requestName, $modelName): string
    {
        $directory = base_path(config('cubeta-starter.request_path'))."/$modelName";

        ensureDirectoryExists($directory);

        return $directory."/$requestName".'.php';
    }
}
