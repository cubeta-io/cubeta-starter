<?php

namespace Cubeta\CubetaStarter\Commands;

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
use Cubeta\CubetaStarter\Settings\Settings;
use Exception;
use Illuminate\Console\Command;
use Throwable;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class BaseCommand extends Command
{
    public function handleCommandLogsAndErrors(): void
    {
        foreach (CubeLog::logs() as $log) {
            if ($log instanceof Exception or $log instanceof Throwable) {
                $this->newLine();
                error("Message : {$log->getMessage()} \nFile: {$log->getFile()}\nLine: {$log->getLine()}\n");
                $this->newLine();
            } elseif ($log instanceof CubeError) {
                $this->newLine();
                error("Error : {$log->message}");
                if ($log->affectedFilePath) {
                    $this->newLine();
                    $this->line("Affected Path : {$log->affectedFilePath}");
                    $this->newLine();
                }
                if ($log->happenedWhen) {
                    $this->newLine();
                    $this->line("Happened When : {$log->happenedWhen}");
                    $this->newLine();
                }
                $this->newLine();
            } else if ($log instanceof CubeInfo) {
                $this->newLine();
                info($log->getMessage());
                $this->newLine();
            } elseif ($log instanceof CubeWarning) {
                $this->newLine();
                warning($log->getMessage());
                $this->newLine();
            } elseif (is_string($log)) {
                $this->newLine();
                $this->line($log);
                $this->newLine();
            }
        }

        CubeLog::flush();
    }

    public function askForContainer(): array|string
    {
        return select(
            label: "What Is The Container Type For This Operation",
            options: ContainerType::ALL,
            default: ContainerType::API
        );
    }

    public function askForOverride(): bool
    {
        return confirm(
            label: "Do You Want The Generated Files To Override Any Files Of The Same Name ?",
        );
    }

    public function askForActorsAndPermissions(): array
    {
        $actor = $this->askWithoutEmptyAnswer("What Is The Actor Name ?", placeholder: "i.e:admin , customer , ...");
        $hasPermissions = $this->confirm("Does This Actor Has A Specific Permissions You Want o Specify ? ({$actor})", false);
        if ($hasPermissions) {
            $permissions = $this->askWithoutEmptyAnswer(
                "What Are ($actor) Permissions ?",
                placeholder: "i.e : can-read,can-index,can-edit",
                hint: "Write As Many Permissions You Want Just Keep Between Every Permissions And The Another A Comma"
            );
            $permissions = explode(",", $permissions);
        }

        return [
            "actor" => $actor,
            "permissions" => $permissions ?? null
        ];
    }

    protected function askWithoutEmptyAnswer(string $question, ?string $default = null, ?string $placeholder = null, ?string $hint = null): string
    {
        return text(
            label: $question,
            placeholder: $placeholder ?? "",
            default: $default ?? "",
            validate: fn(string $value) => match (true) {
                trim($value) == "" => 'Invalid Input Try Again',
                default => null
            },
            hint: $hint ?? "",
        );
    }

    public function askForModelName(string $class): string
    {
        if (!Settings::make()->getFrontendType()) {
            $frontend = select(
                label: "Chose Your Front-End Stack First",
                options: FrontendTypeEnum::getAllValues(),
                default: FrontendTypeEnum::BLADE->value,
            );
            Settings::make()->setFrontendType(FrontendTypeEnum::tryFrom($frontend) ?? FrontendTypeEnum::NONE);
        }
        return $this->askWithoutEmptyAnswer("What Is The Model Name For This {$class}");
    }

    public function askForGeneratedFileActors(string $class): array|string|null
    {
        $roleEnumPath = CubePath::make("app/Enums/RolesPermissionEnum.php");

        if ($roleEnumPath->exist() and class_exists("\\App\\Enums\\RolesPermissionEnum")) {
            return select(
                "Who Is The Actor For This $class ?",
                ['none', ...\App\Enums\RolesPermissionEnum::ALLROLES],
                default: "none",
            );
        }

        return null;
    }

    public function askForRelations(string $modelName): array
    {
        $createdModels = Settings::make()->getAllModels();
        $relations = [];

        $itHasMany = confirm(
            label: "Does ({$modelName}) model related with another model by <fg=blue>has many</fg=blue> relation ?",
            default: false
        );

        while ($itHasMany) {
            $relatedModel = suggest(
                'What is the name of the related model table ?',
                $createdModels,
                validate: fn(string $value) => match (true) {
                    trim($value) == "" => 'Invalid Input Try Again',
                    default => null
                },
            );

            $relations[$relatedModel] = RelationsTypeEnum::HasMany->value;

            $itHasMany = confirm('Does it has another <fg=blue>has many</fg=blue> relation ? ', false);
        }

        $itManToMany = confirm("Does ({$modelName}) model related with another model by <fg=blue>many to many</fg=blue> relation ?", false);

        while ($itManToMany) {
            $relatedModel = suggest(
                'What is the name of the related model table ?',
                $createdModels,
                validate: fn(string $value) => match (true) {
                    trim($value) == "" => 'Invalid Input Try Again',
                    default => null
                },
            );

            $relations[$relatedModel] = RelationsTypeEnum::ManyToMany->value;

            $itManToMany = confirm("Does it has another <fg=blue>many to many</fg=blue> relation ? ", false);
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
        $paramsString = text(
            label: 'Enter your model columns',
            placeholder: "ie: name,started_at,...",
            validate: fn(string $value) => match (true) {
                trim($value) == "" => 'Invalid Input Try Again',
                default => null
            }
        );

        $paramsString = explode(',', $paramsString);
        $attributes = [];
        foreach ($paramsString as $field) {
            $field = Naming::column($field);
            $type = select(
                label: "What is the data type of the (( {$field} field )) ?",
                options: ColumnTypeEnum::getAllValues(),
                default: 5,
            );
            $attributes[$field] = $type;

            if ($getNullables) {
                if (confirm("Is This Column Nullable ?" , false)) {
                    $nullables[] = $field;
                }
            }

            if ($getUniques) {
                if (confirm("Is This Column Unique ?", false)) {
                    $uniques[] = $field;
                }
            }
        }

        return [$attributes, $uniques, $nullables];
    }
}
