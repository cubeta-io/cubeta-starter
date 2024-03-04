<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeInfo;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\CubeWarning;
use Exception;
use Illuminate\Console\Command;
use Throwable;

class BaseCommand extends Command
{
    public function handleCommandLogsAndErrors(): void
    {
        foreach (CubeLog::logs() as $log) {
            if ($log instanceof Exception or $log instanceof Throwable) {
                $this->error("Message : {$log->getMessage()} \nFile: {$log->getFile()}\nLine: {$log->getLine()}\n");
            } elseif ($log instanceof CubeError) {
                $this->error($log->getMessage());
            } else if ($log instanceof CubeInfo) {
                $this->info($log->getMessage());
            } elseif ($log instanceof CubeWarning) {
                $this->warn($log->getMessage());
            } elseif (is_string($log)) {
                $this->info($log);
            }
        }

        CubeLog::flush();
    }
}
