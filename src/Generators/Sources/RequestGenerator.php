<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;

class RequestGenerator extends AbstractGenerator
{
    public static string $key = 'request';
    public static string $configPath = 'cubeta-starter.request_path';

    public function run(bool $override = false): void
    {
        $rules = $this->generateRules();

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.request_namespace') . "\\{$this->table->modelName}",
            '{class}' => $this->table->modelName,
            '{rules}' => $rules['rules'],
            '{prepareForValidation}' => $rules['prepare_for_validation']
        ];

        $requestPath = $this->table->getRequestPath();

        if ($requestPath->exist()) {
            $requestPath->logAlreadyExist("Generating FormRequest For : ({$this->table->modelName})");
            return;
        }

        $requestPath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $requestPath->fullPath);

        if ($this->table->translatables()->count()) {
            FileUtils::addImportStatement("use App\Rules\LanguageShape; \n", $requestPath);
        }

        $requestPath->format();
    }

    private function generateRules(): array
    {
        $rules = '';

        $translatableAttributeCount = $this->table->translatables()->count();
        $prepareForValidation = $translatableAttributeCount > 0 ? "protected function prepareForValidation()\n{\nif (request()->acceptsHtml()){\$this->merge([\n" : '';

        foreach ($this->table->attributes as $attribute) {

            $isNullable = $attribute->nullable ? "nullable" : "required";
            $isUnique = $attribute->unique ? "unique:" . $this->table->tableNaming() . "," . $attribute->name : '';

            if ($attribute->type == ColumnTypeEnum::STRING->value && in_array($attribute->name, ['name', 'first_name', 'last_name', 'email', 'password', 'phone', 'phone_number', 'number',])) {
                $rules .= match ($attribute->name) {
                    'name', 'first_name', 'last_name' => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|string|min:3|max:255',\n",
                    'email' => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|string|max:255|email',\n",
                    'password' => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|string|max:255|min:6|confirmed',\n",
                    'phone', 'phone_number', 'number' => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|string|max:255|min:6',\n",
                };
                continue;
            }

            if ($attribute->isTranslatable()) {
                $prepareForValidation .= "'{$attribute->name}' => json_encode(\$this->{$attribute->name}), \n";
            }

            $rules .= match ($attribute->type) {
                ColumnTypeEnum::TRANSLATABLE->value => "\t\t\t'{$attribute->name}'=>['{$isUnique}' , '{$isNullable}', 'json', new LanguageShape] , \n",
                ColumnTypeEnum::STRING->value => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|string|min:3|max:255',\n",
                ColumnTypeEnum::DATE->value,
                ColumnTypeEnum::DATETIME->value,
                ColumnTypeEnum::TIMESTAMP->value => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|date',\n",
                ColumnTypeEnum::TIME->value => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|date_format:H:i',\n",
                ColumnTypeEnum::BOOLEAN->value => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|boolean',\n",
                ColumnTypeEnum::KEY->value => "\t\t\t'{$attribute->name}'=>'{$isNullable}|numeric|exists:{$attribute->tableNaming(str_replace('_id', '', $attribute->name))},id',\n",
                ColumnTypeEnum::FILE->value => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|image|mimes:jpeg,png,jpg|max:2048',\n",
                ColumnTypeEnum::TEXT->value => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|string',\n",
                ColumnTypeEnum::INTEGER->value,
                ColumnTypeEnum::BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_DOUBLE->value,
                ColumnTypeEnum::DOUBLE->value,
                ColumnTypeEnum::FLOAT->value => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|numeric',\n",
                default => "\t\t\t'{$attribute->name}'=>'{$isUnique}|{$isNullable}|{$attribute->type}',\n",
            };
        }

        $prepareForValidation .= $translatableAttributeCount > 0 ? "]);\n}\n}" : '';

        return [
            'rules' => $rules,
            'prepare_for_validation' => $prepareForValidation,
        ];
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/request.stub';
    }
}
