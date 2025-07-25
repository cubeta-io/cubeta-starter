<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Requests;

use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class ValidationRuleString
{
    public string $rule;

    /**
     * @var PhpImportString[]|null
     */
    public ?array $import = null;

    /**
     * @param string                 $rule
     * @param PhpImportString[]|null $import
     */
    public function __construct(string $rule, ?array $import = null)
    {
        $this->rule = $rule;
        $this->import = $import;
    }

    public function __toString(): string
    {
        if ($this->import) {
            return $this->rule;
        }

        return "'$this->rule'";
    }
}