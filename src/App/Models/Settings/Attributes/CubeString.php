<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;

class CubeString extends CubeStringable implements HasFakeMethod
{
    public function fakeMethod(): FakeMethodString
    {
        $isUnique = $this->unique ? "->unique()" : "";
        $fakeMethod = $this->guessStringMethod();
        return new FakeMethodString(
            $this->name,
            "fake(){$isUnique}->{$fakeMethod}()",
        );
    }
}