<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\PropertyValidationRuleString;

interface HasPropertyValidationRule
{
    public function propertyValidationRule(): PropertyValidationRuleString;
}