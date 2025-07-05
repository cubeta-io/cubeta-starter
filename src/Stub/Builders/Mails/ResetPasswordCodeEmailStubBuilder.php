<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Mails;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class ResetPasswordCodeEmailStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Mails/ResetPasswordCodeEmail.stub');
    }
}