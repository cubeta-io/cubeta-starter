<?php

namespace Cubeta\CubetaStarter\StringValues\Contracts\Factories;

use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;

interface HasFakeMethod
{
    public function fakeMethod(): FakeMethodString;
}