<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;

class CubeTranslatable extends CubeStringable implements HasFakeMethod
{
    public function fakeMethod(): FakeMethodString
    {
        $method = $this->guessStringMethod();
        return new FakeMethodString(
            $this->name,
            "Translatable::fake('$method')",
            new ImportString("App\\Serializers\\Translatable")
        );
    }
}