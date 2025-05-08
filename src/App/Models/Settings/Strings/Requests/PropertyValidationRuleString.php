<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;

class PropertyValidationRuleString
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var ValidationRuleString[]
     */
    public array $rules = [];

    /**
     * @var PhpImportString[]
     */
    public ?array $imports = null;

    /**
     * @param string                 $name
     * @param ValidationRuleString[] $rules
     * @param PhpImportString[]      $imports
     */
    public function __construct(string $name, array $rules, ?array $imports = null)
    {
        $this->name = $name;
        $this->rules = $rules;
        $this->imports = $imports;
        foreach ($this->rules as $rule) {
            if ($rule->import) {
                $this->imports = array_merge($this->imports ?? [], $rule->import);
            }
        }
    }

    public function __toString(): string
    {
        $rules = "";

        foreach ($this->rules as $rule) {
            $rules .= "$rule, ";
        }

        return "'$this->name' => [$rules]";
    }
}