<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Requests;

use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;

interface HasPropertyValidationRule
{
    public function propertyValidationRule(): PropertyValidationRuleString;
}