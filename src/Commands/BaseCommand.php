<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\LogsMessages\CubeError;
use Exception;
use Illuminate\Console\Command;

class BaseCommand extends Command
{
    public function handleCommandLogsAndErrors(Exception|CubeError $logs): void
    {
        foreach ($logs as $log) {
            if ($log instanceof Exception) {
                $this->error("Message : " . $log->getMessage());
                $this->error("Affected File : " . $log->getFile());
            } elseif ($log instanceof CubeError) {
                $this->error($log->getMessage());
            }
        }
    }
}
