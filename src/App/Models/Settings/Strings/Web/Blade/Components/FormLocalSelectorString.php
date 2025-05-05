<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components;

class FormLocalSelectorString
{
    public function __construct()
    {
    }

    public function __toString(): string
    {
        return "<div class=\"m-2 d-flex justify-content-end\">
                    <x-language-selector/>
                </div>";
    }
}