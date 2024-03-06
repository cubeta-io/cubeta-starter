<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ContainerType;
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
                $this->newLine();
            } elseif ($log instanceof CubeError) {
                $this->error("Error : {$log->message}");
                if ($log->affectedFilePath) $this->line("Affected Path : {$log->affectedFilePath}");
                if ($log->happenedWhen) $this->line("Happened When : {$log->happenedWhen}");
                $this->newLine();
            } else if ($log instanceof CubeInfo) {
                $this->info($log->getMessage());
                $this->newLine();
            } elseif ($log instanceof CubeWarning) {
                $this->warn($log->getMessage());
                $this->newLine();
            } elseif (is_string($log)) {
                $this->info($log);
                $this->newLine();
            }
        }

        CubeLog::flush();
    }

    public function askForContainer(): array|string
    {
        return $this->choice("What Is The Container Type For This Operation : ", ContainerType::ALL, ContainerType::API);
    }

    public function askForOverride(): bool
    {
        return $this->confirm("Do You Want The Generated Files To Override Any Files Of The Same Name ?", true);
    }

    public function askForActorsAndPermissions(): array
    {
        $actor = $this->askWithoutEmptyAnswer("What Is The Actor Name ? i.e:admin , customer , ... ");
        $hasPermissions = $this->confirm("Does This Actor Has A Specific Permissions You Want o Specify ? ({$actor})", false);
        if ($hasPermissions) {
            $permissions = $this->askWithoutEmptyAnswer("What Are ($actor) Permissions ? \nWrite As Many Permissions You Want Just Keep Between Every Permissions And The Another A Comma i.e : can-read,can-index,can-edit");
            $permissions = explode(",", $permissions);
        }

        return [
            "actor" => $actor,
            "permissions" => $permissions ?? null
        ];
    }

    protected function askWithoutEmptyAnswer(string $question, ?string $default = null): string
    {
        $answer = '';
        do {
            $answer = $this->ask($question, $default);
            $answer = trim($answer);

            if ($answer == '') {
                $this->error("Invalid Input Try Again");
            }

        } while ($answer == '');

        return $answer;
    }
}
