<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Support\Facades\File;

class PostInstallFunctions
{
    /**
     * Initialize the exception handler
     */
    public static function handleExceptionHandler(): void
    {
        $handlerStub = file_get_contents(__DIR__ . '/stubs/handler.stub');
        $handlerPath = base_path('app/Exceptions/Handler.php');
        if (!file_exists($handlerPath)) {
            File::makeDirectory($handlerPath, 077, true, true);
        }
        file_put_contents($handlerPath, $handlerStub);
    }
}
