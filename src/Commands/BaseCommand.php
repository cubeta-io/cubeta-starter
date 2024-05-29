<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\Naming;
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
                $this->line($log);
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
        do {
            $answer = $this->ask($question, $default);
            $answer = trim($answer);

            if ($answer == '') {
                $this->error("Invalid Input Try Again");
            }

        } while ($answer == '');

        return $answer;
    }

    public function askForModelName(string $class): string
    {
        if (!Settings::make()->getFrontendType()) {
            $frontend = $this->choice("Chose Your Front-End Stack First", FrontendTypeEnum::getAllValues());
            Settings::make()->setFrontendType(FrontendTypeEnum::tryFrom($frontend) ?? FrontendTypeEnum::NONE);
        }
        return $this->askWithoutEmptyAnswer("What Is The Model Name For This {$class}");
    }

    public function askForGeneratedFileActors(string $class): array|string|null
    {
        $roleEnumPath = CubePath::make("app/Enums/RolesPermissionEnum.php");

        if ($roleEnumPath->exist() and class_exists("\\App\\Enums\\RolesPermissionEnum")) {
            return $this->choice("Who Is The Actor For This $class ?", ['none', ...\App\Enums\RolesPermissionEnum::ALLROLES]);
        }

        return null;
    }

    public function askForRelations(string $modelName): array
    {
        $createdModels = Settings::make()->getAllModels();
        $relations = [];

        $itHasMany = $this->confirm("Does ({$modelName}) model related with another model by <fg=blue>has many</fg=blue> relation ?", false);

        while ($itHasMany) {
            $relatedModel = $this->anticipate('What is the name of the related model table ?', $createdModels);

            while (empty(trim($relatedModel))) {
                $this->error('Invalid Input');
                $relatedModel = $this->anticipate('What is the name of the related model table ?', $createdModels);
            }

            $relations[$relatedModel] = RelationsTypeEnum::HasMany->value;

            $itHasMany = $this->confirm('Does it has another <fg=blue>has many</fg=blue> relation ? ', false);
        }

        $itManToMany = $this->confirm("Does ({$modelName}) model related with another model by <fg=blue>many to many</fg=blue> relation ?", false);

        while ($itManToMany) {
            $relatedModel = $this->anticipate("What is the name of the related model table ? ", $createdModels);

            while (empty(trim($relatedModel))) {
                $this->error('Invalid Input');
                $relatedModel = $this->anticipate('What is the name of the related model table ?', $createdModels);
            }

            $relations[$relatedModel] = RelationsTypeEnum::ManyToMany->value;

            $itManToMany = $this->confirm("Does it has another <fg=blue>many to many</fg=blue> relation ? ", false);
        }

        return $relations;
    }

    /**
     * @param bool $getUniques
     * @param bool $getNullables
     * @return array
     */
    public function askForModelAttributes(bool $getUniques = false, bool $getNullables = false): array
    {
        $nullables = [];
        $uniques = [];
        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        while (empty(trim($paramsString))) {
            $this->error('Invalid Input');
            $paramsString = $this->ask('Enter your params like "name,started_at,..."');
        }

        $paramsString = explode(',', $paramsString);
        $attributes = [];
        foreach ($paramsString as $field) {
            $field = Naming::column($field);
            $type = $this->choice(
                "What is the data type of the (( {$field} field )) ? default is ",
                ColumnTypeEnum::getAllValues(),
                6,
            );
            $attributes[$field] = $type;

            if ($getNullables) {
                if ($this->confirm("Is This Column Nullable ?")) {
                    $nullables[] = $field;
                }
            }

            if ($getUniques) {
                if ($this->confirm("Is This Column Unique ?")) {
                    $uniques[] = $field;
                }
            }
        }

        return [$attributes, $uniques, $nullables];
    }
}
