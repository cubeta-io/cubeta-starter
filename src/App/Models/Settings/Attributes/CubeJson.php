<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;

class CubeJson extends CubeStringable implements HasFakeMethod
{
    public function fakeMethod(): FakeMethodString
    {
        $isUnique = $this->unique ? "->unique()" : "";
        return new FakeMethodString(
            $this->name,
            "json_encode([fake(){$isUnique}->word() => fake(){$isUnique}->word()])"
        );
    }
}