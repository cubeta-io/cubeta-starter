<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;

interface HasPropertyValidationRule
{
    public function propertyValidationRule(): PropertyValidationRuleString;
}