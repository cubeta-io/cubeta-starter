<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;

interface HasFakeMethod
{
    public function fakeMethod(): FakeMethodString;
}