<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;

class ValidationRuleString
{
    public string $rule;

    /**
     * @var ImportString[]|null
     */
    public ?array $import = null;

    /**
     * @param string              $rule
     * @param ImportString[]|null $import
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