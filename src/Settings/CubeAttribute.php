<?php

namespace Cubeta\CubetaStarter\Settings;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\Attributes\CubeBoolean;
use Cubeta\CubetaStarter\Settings\Attributes\CubeDate;
use Cubeta\CubetaStarter\Settings\Attributes\CubeDateTime;
use Cubeta\CubetaStarter\Settings\Attributes\CubeDouble;
use Cubeta\CubetaStarter\Settings\Attributes\CubeFile;
use Cubeta\CubetaStarter\Settings\Attributes\CubeFloat;
use Cubeta\CubetaStarter\Settings\Attributes\CubeInteger;
use Cubeta\CubetaStarter\Settings\Attributes\CubeJson;
use Cubeta\CubetaStarter\Settings\Attributes\CubeKey;
use Cubeta\CubetaStarter\Settings\Attributes\CubeString;
use Cubeta\CubetaStarter\Settings\Attributes\CubeText;
use Cubeta\CubetaStarter\Settings\Attributes\CubeTime;
use Cubeta\CubetaStarter\Settings\Attributes\CubeTimestamp;
use Cubeta\CubetaStarter\Settings\Attributes\CubeTranslatable;
use Cubeta\CubetaStarter\Settings\Attributes\CubeUnsignedBigInteger;
use Cubeta\CubetaStarter\StringValues\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeDisplayComponent;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\Traits\NamingConventions;

/**
 *
 */
class CubeAttribute implements HasResourcePropertyString, HasBladeDisplayComponent
{
    use NamingConventions;

    public ?string $parentTableName;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var bool
     */
    public bool $nullable;

    /**
     * @var bool
     */
    public bool $unique;

    public bool $isRequired;

    /**
     * @param string      $name
     * @param string      $type
     * @param bool        $nullable
     * @param bool        $unique
     * @param string|null $parentTableName
     */
    public function __construct(string $name, string $type, bool $nullable = false, bool $unique = false, ?string $parentTableName = null)
    {
        $this->name = Naming::column($name);
        $this->type = $type;
        $this->nullable = $nullable;
        $this->unique = $unique;
        $this->usedString = $this->name;
        $this->parentTableName = $parentTableName;
        $this->isRequired = !$this->nullable;
    }

    /**
     * @return bool|string
     */
    public function toJson(): bool|string
    {
        return json_encode([
            "name" => $this->name,
            "type" => $this->type,
            "nullable" => $this->nullable,
            "unique" => $this->unique,
        ], JSON_PRETTY_PRINT);
    }

    /**
     * @return array{name:string , type:string , nullable:boolean , unique:boolean}
     */
    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "type" => $this->type,
            "nullable" => $this->nullable,
            "unique" => $this->unique,
        ];
    }

    /**
     * @return bool
     */
    public function isKey(): bool
    {
        return $this->type == ColumnTypeEnum::KEY->value;
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return $this->type == ColumnTypeEnum::FILE->value;
    }

    /**
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return $this->type == ColumnTypeEnum::TRANSLATABLE->value;
    }

    /**
     * @return bool
     */
    public function isDateTime(): bool
    {
        return $this->type == ColumnTypeEnum::DATETIME->value;
    }

    public function isDateable(): bool
    {
        return ColumnTypeEnum::isDateTimeType($this->type);
    }

    public function isDate(): bool
    {
        return ColumnTypeEnum::DATE->value == $this->type;
    }

    /**
     * @return bool
     */
    public function isString(): bool
    {
        return in_array($this->type, [
            ColumnTypeEnum::STRING->value,
            ColumnTypeEnum::TEXT->value,
            ColumnTypeEnum::JSON->value,
            ColumnTypeEnum::TRANSLATABLE->value,
        ]);
    }

    /**
     * @return bool
     */
    public function isNumeric(): bool
    {
        return in_array($this->type, [
            ColumnTypeEnum::INTEGER->value,
            ColumnTypeEnum::BIG_INTEGER->value,
            ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
            ColumnTypeEnum::DOUBLE->value,
            ColumnTypeEnum::FLOAT->value,
        ]);
    }

    /**
     * @return bool
     */
    public function isBoolean(): bool
    {
        return $this->type == ColumnTypeEnum::BOOLEAN->value;
    }

    /**
     * @return bool
     */
    public function isText(): bool
    {
        return $this->type == ColumnTypeEnum::TEXT->value;
    }

    /**
     * @return bool
     */
    public function isTextable(): bool
    {
        return str($this->name)->contains(['body', 'content', 'description', 'summary', 'post', 'note', 'message'])
            && $this->isText();
    }

    /**
     * @return CubeTable
     */
    public function getOwnerTable(): CubeTable
    {
        return Settings::make()->getTable($this->parentTableName) ?? CubeTable::create($this->parentTableName);
    }

    public function labelNaming(): string
    {
        if ($this->isBoolean()) {
            return str_starts_with($this->name, 'is_') ? $this->titleNaming() . " ?"
                : $this->titleNaming();
        } else {
            return $this->titleNaming();
        }
    }

    public static function factory(string $name, string $type, bool $nullable = false, bool $unique = false, ?string $parentTableName = null): CubeText|CubeInteger|CubeFloat|CubeDateTime|CubeTranslatable|CubeTimestamp|CubeDate|CubeAttribute|CubeKey|CubeJson|CubeString|CubeBoolean|CubeFile|CubeDouble|CubeTime|CubeUnsignedBigInteger
    {
        $type = ColumnTypeEnum::tryFrom($type);

        return match ($type) {
            ColumnTypeEnum::STRING => new CubeString($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::TEXT => new CubeText($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::JSON => new CubeJson($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::INTEGER, ColumnTypeEnum::BIG_INTEGER => new CubeInteger($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::UNSIGNED_BIG_INTEGER => new CubeUnsignedBigInteger($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::DOUBLE => new CubeDouble($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::FLOAT => new CubeFloat($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::BOOLEAN => new CubeBoolean($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::DATE => new CubeDate($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::TIME => new CubeTime($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::DATETIME => new CubeDateTime($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::TIMESTAMP => new CubeTimestamp($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::FILE => new CubeFile($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::KEY => new CubeKey($name, $type->value, $nullable, $unique, $parentTableName),
            ColumnTypeEnum::TRANSLATABLE => new CubeTranslatable($name, $type->value, $nullable, $unique, $parentTableName),
            default => new CubeAttribute($name, $type->value, $nullable, $unique, $parentTableName),
        };
    }

    /**
     * @return ValidationRuleString[]
     */
    protected function uniqueOrNullableValidationRules(): array
    {
        $rules = [new ValidationRuleString($this->nullable ? 'nullable' : 'required')];

        if ($this->unique) {
            $routeParameter = $this->getOwnerTable()->routeParameterNaming();
            $rules[] = new ValidationRuleString(
                "Rule::unique('{$this->parentTableName}','{$this->name}')->when(\$this->method() == 'PUT', fn(\$rule) => \$rule->ignore(\$this->route('$routeParameter')))",
                [new PhpImportString("Illuminate\Validation\Rule")]
            );
        }

        return $rules;
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            $this->name
        );
    }

    public function bladeDisplayComponent(): DisplayComponentString
    {
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);
        $modelVariable = $table->variableNaming();
        $label = $this->labelNaming();
        return new DisplayComponentString(
            "x-small-text-field",
            [
                [
                    "key" => ":value",
                    "value" => "\${$modelVariable}->{$this->name}"
                ],
                [
                    "key" => 'label',
                    'value' => $label
                ]
            ]
        );
    }
}
