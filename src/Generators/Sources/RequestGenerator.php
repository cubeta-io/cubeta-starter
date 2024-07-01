<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\FileUtils;

class RequestGenerator extends AbstractGenerator
{
    public static string $key = 'request';

    public function run(bool $override = false): void
    {
        $rules = $this->generateRules();

        $stubProperties = [
            '{namespace}'            => $this->table->getRequestNameSpace(false, true),
            '{class}'                => $this->table->modelName,
            '{rules}'                => $rules['rules'],
            '{prepareForValidation}' => $rules['prepare_for_validation'],
            "{updateRules}"          => $rules["updateRules"],
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

        $prepareForValidation = "protected function prepareForValidation()\n{\nif (request()->acceptsHtml()){\$this->merge([\n";

        foreach ($this->table->attributes as $attribute) {

            $isNullable = $attribute->nullable ? "nullable" : "required";
            $isUnique = $attribute->unique ? " , 'unique:" . $this->table->tableNaming() . "," . $attribute->name . "'," : '';

            if ($attribute->type == ColumnTypeEnum::STRING->value && in_array($attribute->name, ['name', 'first_name', 'last_name', 'email', 'password', 'phone', 'phone_number', 'number'])) {
                $rules .= match ($attribute->name) {
                    'name', 'first_name', 'last_name' => "\t\t\t'{$attribute->name}'=>['{$isNullable}','string','min:3','max:255'{$isUnique}],\n",
                    'email' => "\t\t\t'{$attribute->name}'=>'['{$isNullable}','string','max:255','email'{$isUnique}],\n",
                    'password' => "\t\t\t'{$attribute->name}'=>['{$isNullable}','string','max:255','min:6','confirmed'{$isUnique}],\n",
                    'phone', 'phone_number', 'number' => "\t\t\t'{$attribute->name}'=>['{$isNullable}','string','max:255','min:6'{$isUnique}],\n",
                };
                continue;
            }

            if ($attribute->isTranslatable()
                && ContainerType::isWeb($this->generatedFor)
                && $this->frontType == FrontendTypeEnum::BLADE
            ) {
                $prepareForValidation .= "'{$attribute->name}' => json_encode(\$this->{$attribute->name}), \n";
            }

            $rules .= match ($attribute->type) {
                ColumnTypeEnum::TRANSLATABLE->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}', 'json', new LanguageShape{$isUnique}] , \n",
                ColumnTypeEnum::STRING->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','string','min:3','max:255'{$isUnique}],\n",
                ColumnTypeEnum::DATE->value,
                ColumnTypeEnum::DATETIME->value,
                ColumnTypeEnum::TIMESTAMP->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','date'{$isUnique}],\n",
                ColumnTypeEnum::TIME->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','date_format:H:i'{$isUnique}],\n",
                ColumnTypeEnum::BOOLEAN->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','boolean'{$isUnique}],\n",
                ColumnTypeEnum::KEY->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','numeric','exists:{$attribute->tableNaming(str_replace('_id', '', $attribute->name))},id'{$isUnique}],\n",
                ColumnTypeEnum::FILE->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','image','mimes:jpeg,png,jpg','max:2048'{$isUnique}],\n",
                ColumnTypeEnum::TEXT->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','string'{$isUnique}],\n",
                ColumnTypeEnum::INTEGER->value,
                ColumnTypeEnum::BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                ColumnTypeEnum::UNSIGNED_DOUBLE->value,
                ColumnTypeEnum::DOUBLE->value,
                ColumnTypeEnum::FLOAT->value => "\t\t\t'{$attribute->name}'=>['{$isNullable}','numeric'{$isUnique}],\n",
                default => "\t\t\t'{$attribute->name}'=>['{$isNullable}','{$attribute->type}'{$isUnique}],\n",
            };
        }

        $prepareForValidation .= "]);\n}\n}";

        return [
            'rules'                  => $rules,
            'prepare_for_validation' => $prepareForValidation,
            'updateRules'            => str_replace('required', 'nullable', $rules),
        ];
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/request.stub';
    }
}
