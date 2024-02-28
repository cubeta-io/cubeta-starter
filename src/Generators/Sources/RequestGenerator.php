<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Error;
use Throwable;

class RequestGenerator extends AbstractGenerator
{
    public static string $key = 'request';
    public static string $configPath = 'cubeta-starter.request_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $requestName = $this->generatedFileName();

        $rules = $this->generateRules();
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.request_namespace') . "\\{$modelName}",
            '{class}' => $modelName,
            '{rules}' => $rules['rules'],
            '{prepareForValidation}' => $rules['prepare_for_validation']
        ];

        $requestPath = $this->getGeneratingPath($requestName);

        throw_if(file_exists($requestPath), new Error("{$requestName} Already Exists"));

        $this->generateFileFromStub($stubProperties, $requestPath);

        if (in_array(ColumnTypeEnum::TRANSLATABLE->value, $this->attributes)) {
            $this->addImportStatement("use App\Rules\LanguageShape; \n", $requestPath);
        }

        $this->formatFile($requestPath);
    }

    public function generatedFileName(): string
    {
        return 'StoreUpdate' . $this->modelName($this->fileName) . 'Request';
    }

    protected function getAdditionalPath(): string
    {
        return "/" . $this->modelName($this->fileName);
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/request.stub';
    }

    private function generateRules(): array
    {
        $rules = '';

        $translatableAttributeCount = count(array_keys($this->attributes, ColumnTypeEnum::TRANSLATABLE->value));
        $prepareForValidation = $translatableAttributeCount > 0 ? "protected function prepareForValidation()\n{\nif (request()->acceptsHtml()){\$this->merge([\n" : '';

        $modelName = $this->modelName($this->fileName);
        foreach ($this->attributes as $name => $type) {

            $name = $this->columnName($name);
            $isNullable = in_array($name, $this->nullables) ? "nullable" : "required";
            $isUnique = in_array($name, $this->uniques) ? "unique:" . $modelName . "," . $name : '';

            if ($type == ColumnTypeEnum::STRING->value && in_array($name, ['name', 'first_name', 'last_name', 'email', 'password', 'phone', 'phone_number', 'number',])) {
                $rules .= match ($name) {
                    'name', 'first_name', 'last_name' => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|min:3|max:255',\n",
                    'email' => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|max:255|email',\n",
                    'password' => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|max:255|min:6|confirmed',\n",
                    'phone', 'phone_number', 'number' => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|max:255|min:6',\n",
                };
                continue;
            }

            if ($type == ColumnTypeEnum::TRANSLATABLE->value) {
                $prepareForValidation .= "'{$name}' => json_encode(\$this->{$name}), \n";
            }

            $rules .= match ($type) {
                ColumnTypeEnum::TRANSLATABLE->value => "\t\t\t'{$name}'=>['{$isUnique}' , '{$isNullable}', 'json', new LanguageShape] , \n",
                ColumnTypeEnum::STRING->value => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string|min:3|max:255',\n",
                ColumnTypeEnum::DATE->value,
                ColumnTypeEnum::DATETIME->value,
                ColumnTypeEnum::TIMESTAMP->value => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|date',\n",
                ColumnTypeEnum::TIME->value => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|date_format:H:i',\n",
                ColumnTypeEnum::BOOLEAN->value => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|boolean',\n",
                ColumnTypeEnum::KEY->value => "\t\t\t'{$name}'=>'{$isNullable}|numeric|exists:{$this->tableName(str_replace('_id', '', $name))},id',\n",
                ColumnTypeEnum::FILE->value => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|image|mimes:jpeg,png,jpg|max:2048',\n",
                ColumnTypeEnum::TEXT->value => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|string',\n",
                ColumnTypeEnum::INTEGER->value,
                ColumnTypeEnum::BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_DOUBLE->value,
                ColumnTypeEnum::DOUBLE->value,
                ColumnTypeEnum::FLOAT->value => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|numeric',\n",
                default => "\t\t\t'{$name}'=>'{$isUnique}|{$isNullable}|{$type}',\n",
            };
        }

        $prepareForValidation .= $translatableAttributeCount > 0 ? "]);\n}\n}" : '';

        return [
            'rules' => $rules,
            'prepare_for_validation' => $prepareForValidation,
        ];
    }
}
