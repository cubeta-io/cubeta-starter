<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Enums\ValidationTypeEnum;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\Settings;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

class BaseCommand extends Command
{
    protected function argumentFormatsHelp(): string
    {
        $columnTypes = implode(', ', ColumnTypeEnum::getAllValues());
        $relationTypes = implode(', ', RelationsTypeEnum::getAllValues());

        return <<<HELP
          Argument formats (used so this command can be run in one non-interactive line, e.g. by an AI agent):

            attributes   "field:type,field2:type2,..."
                         Supported types: {$columnTypes}
                         Example: "title:string,body:text,is_published:boolean,category_id:key"
                         Note: a "key" column (e.g. category_id) automatically creates a belongsTo relation.

            relations    "relatedModel:relationType,relatedModel2:relationType2,..."
                         Supported relation types: {$relationTypes}
                         Example: "comments:hasMany,tags:manyToMany"

            nullables    "field,field2,..." - comma separated column names that are nullable.
            uniques      "field,field2,..." - comma separated column names that are unique.
            container    api | web | both

          Skip prompts entirely:
            Pass every argument explicitly and add --no-interaction (falls back to sane
            defaults instead of asking) and --force (to overwrite existing files, otherwise
            existing files are left untouched when non-interactive).
          HELP;
    }

    public function askForContainer(): array|string
    {
        if (!$this->input->isInteractive()) {
            return ContainerType::API;
        }

        return select(
            label: "What Is The Container Type For This Operation",
            options: ContainerType::ALL,
            default: ContainerType::API
        );
    }

    /**
     * ask the user about the classes he wants to validate the requests data with
     * then store his choice within the package settings file
     * @return ValidationTypeEnum
     */
    public function askForValidationType(): ValidationTypeEnum
    {
        if (!$this->input->isInteractive()) {
            return Settings::make()->getValidationType();
        }

        $choice = select(
            label: "What Do You Want To Validate The Requests Data With ?",
            options: ValidationTypeEnum::getAllValues(),
            default: Settings::make()->getValidationType()->value,
            hint: "The DTOs are generated using [wendelladriel/laravel-validated-dto] package and it will be installed for you"
        );

        $type = ValidationTypeEnum::tryFrom($choice) ?? ValidationTypeEnum::FORM_REQUEST;

        Settings::make()->setValidationType($type);

        return $type;
    }

    public function askForOverride(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (!$this->input->isInteractive()) {
            return false;
        }

        return confirm(
            label: "Do You Want The Generated Files To Override Any Files Of The Same Name ?",
        );
    }

    /**
     * Parse a CLI "field:type,field2:type2" string into the assoc [field => type] shape
     * the generators expect. Already-resolved arrays (e.g. forwarded from another command
     * via `$this->call()`) are passed through untouched.
     */
    public function resolveAttributes(array|string|null $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!$value) {
            return [];
        }

        $attributes = [];
        foreach (explode(',', $value) as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }
            [$field, $type] = array_pad(explode(':', $pair, 2), 2, ColumnTypeEnum::STRING->value);
            $attributes[Naming::column(trim($field))] = trim($type);
        }

        return $attributes;
    }

    /**
     * Parse a CLI "model:relationType, model2:relationType2" string into the assoc
     * [relatedModel => relationType] shape the generators expect.
     */
    public function resolveRelations(array|string|null $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!$value) {
            return [];
        }

        $relations = [];
        foreach (explode(',', $value) as $pair) {
            $pair = trim($pair);
            if (empty($pair)) {
                continue;
            }
            [$model, $type] = array_pad(explode(':', $pair, 2), 2, RelationsTypeEnum::BelongsTo->value);
            $relations[trim($model)] = trim($type);
        }

        return $relations;
    }

    /**
     * Parse a CLI comma separated "field,field2" string into a plain array,
     * used for the nullable/unique column lists.
     */
    public function resolveList(array|string|null $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!$value) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $value)),
            fn(string $item) => $item !== ''
        ));
    }

    public function askForActorsAndPermissions(): array
    {
        $actor = $this->askWithoutEmptyAnswer("What Is The Actor Name ?", placeholder: "i.e:admin , customer , ...");
        $hasPermissions = $this->input->isInteractive()
            && confirm("Does This Actor Has A Specific Permissions You Want o Specify ? ({$actor})", false);
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
        if (!$this->input->isInteractive() && trim($default ?? '') === '') {
            throw new \RuntimeException("Missing required input for a non-interactive run: \"{$question}\". Pass it explicitly as a command argument/option.");
        }

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
        if (!Settings::make()->getFrontendType() && $this->input->isInteractive()) {
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
            if (!$this->input->isInteractive()) {
                return "none";
            }

            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            /** @noinspection PhpUndefinedNamespaceInspection */
            return select(
                "Who Is The Actor For This $class ?",
                ['none', ...\App\Enums\RolesPermissionEnum::ALL_ROLES],
                default: "none",
            );
        }

        return null;
    }

    public function askForRelations(string $modelName): array
    {
        if (!$this->input->isInteractive()) {
            return [];
        }

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
        if (!$this->input->isInteractive()) {
            throw new \RuntimeException("Missing required \"attributes\" input for a non-interactive run. Pass it explicitly as a command argument, e.g. \"name:string,age:integer\".");
        }

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
                if (confirm("Is This Column Nullable ?", false)) {
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
