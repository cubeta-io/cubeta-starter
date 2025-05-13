<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories;

use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;

interface HasFakeMethod
{
    public function fakeMethod(): FakeMethodString;
}