<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeRequest extends Command
{
    use AssistCommand;

    public $description = 'Create a new request';

    public $signature = 'create:request
        {name : The name of the model }
        {attributes? : columns with data types}
        {nullables? : nullable columns}
        {uniques? : uniques columns}';

    /**
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $nullables = $this->argument('nullables') ?? [];
        $uniques = $this->argument('uniques') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createRequest($modelName, $attributes, $nullables, $uniques);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createRequest($modelName, array $attributes = [], array $nullables = [], array $uniques = []): void
    {
        $modelName = modelNaming($modelName);
        $requestName = $this->getRequestName($modelName);

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.request_namespace') . "\\{$modelName}",
            '{class}' => $modelName,
            '{rules}' => $this->generateCols($attributes, $nullables, $uniques, $modelName),
            '{prepareForValidation}' => $this->getPrepareForValidationMethod($attributes)
        ];

        $requestPath = $this->getRequestPath($requestName, $modelName);
        if (file_exists($requestPath)) {
            $this->error("{$requestName} Already Exists");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $requestPath,
            __DIR__ . '/stubs/request.stub'
        );

        if (in_array('translatable', $attributes)) {
            addImportStatement("use App\Rules\LanguageShape; \n", $requestPath);
        }

        $this->formatFile($requestPath);
        $this->info("Created request: {$requestName}");
    }

    private function getRequestName($modelName): string
    {
        return 'StoreUpdate' . $modelName . 'Request';
    }

    private function generateCols(array $attributes = [], array $nullables = [], array $uniques = [], string $modelName = null): string
    {
        $rules = '';
        foreach ($attributes as $name => $type) {

            $name = columnNaming($name);
            $isNullable = in_array($name, $nullables) ? "nullable" : "required";
            $isUnique = in_array($name, $uniques) ? "unique:" . tableNaming($modelName) . "," . columnNaming($name) : '';

            if ($type == 'translatable') {
                $rules .= "\t\t\t'{$name}'=>['{$isUnique}' , '{$isNullable}', 'json', new LanguageShape] , \n";
            } elseif (($name == 'name' || $name == 'first_name' || $name == 'last_name') && $type == 'string') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|min:3|max:255',\n";
            } elseif ($name == 'email' && $type == 'string') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|max:255|email',\n";
            } elseif ($name == 'password' && $type == 'string') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|max:255|min:6|confirmed',\n";
            } elseif (($name == 'phone' || $name == 'phone_number' || $name == 'number') && $type == 'string') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|max:255|min:6',\n";
            } elseif (in_array($type, ['date', 'dateTime', 'timestamp'])) {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|date',\n";
            } elseif ($type == 'time') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|date_format:H:i',\n";
            } elseif ($type == 'boolean') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|boolean',\n";
            } elseif ($type == 'key') {
                $relationModel = str_replace('_id', '', $name);
                $relationModelPluralName = tableNaming($relationModel);
                $rules .= "\t\t\t'{$name}'=>'{$isNullable}|numeric|exists:{$relationModelPluralName},id',\n";
            } elseif ($type == 'file') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|image|mimes:jpeg,png,jpg|max:2048',\n";
            } elseif ($type == 'text') {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string',\n";
            } elseif (in_array($type, ['integer', 'bigInteger', 'unsignedBigInteger', 'unsignedDouble', 'double', 'float'])) {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|numeric',\n";
            } else {
                $rules .= "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|{$type}',\n";
            }
        }

        return $rules;
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function getPrepareForValidationMethod(array $attributes): string
    {
        $translatedAttributes = array_keys($attributes, 'translatable');
        $method = '';
        if (count($translatedAttributes) > 0) {
            $method .= "protected function prepareForValidation()
                        {
                            if (request()->acceptsHtml()){
                                \$this->merge([\n";

            foreach ($translatedAttributes as $attr) {
                $attr = columnNaming($attr);
                $method .= "'{$attr}' => json_encode(\$this->{$attr}), \n";
            }

            $method .= "]);
                            }
                        }";
        }

        return $method;
    }

    private function getRequestPath($requestName, $modelName): string
    {
        $directory = base_path(config('cubeta-starter.request_path')) . "/{$modelName}";

        ensureDirectoryExists($directory);

        return $directory . "/{$requestName}" . '.php';
    }
}
