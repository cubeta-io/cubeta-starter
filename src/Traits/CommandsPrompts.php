<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Console\Command;

trait CommandsPrompts
{
    use SettingsHandler;

    /**
     * get model relations from the user using the command line
     * @return array
     */
    public function getRelationsFromPrompts(): array
    {
        $createdModels = $this->getAllModelsName();
        $relations = [];

        $itHasMany = Command::confirm("Does this model related with another model by <fg=red>has many</fg=red> relation ?", false);

        while ($itHasMany) {
            $relatedModel = Command::anticipate('What is the name of the related model table ?', $createdModels);

            while (empty(trim($relatedModel))) {
                Command::error('Invalid Input');
                $relatedModel = Command::anticipate('What is the name of the related model table ?', $createdModels);
            }

            $relations[$relatedModel] = RelationsTypeEnum::HasMany->value;

            $itHasMany = Command::confirm('Does it has another <fg=red>has many</fg=red> relation ? ', false);
        }

        $itManToMany = Command::confirm("Does this model related with another model by <fg=red>many to many</fg=red> relation ?", false);

        while ($itManToMany) {
            $relatedModel = Command::anticipate("What is the name of the related model table ? ", $createdModels);

            while (empty(trim($relatedModel))) {
                Command::error('Invalid Input');
                $relatedModel = Command::anticipate('What is the name of the related model table ?', $createdModels);
            }

            $relations[$relatedModel] = RelationsTypeEnum::ManyToMany->value;

            $itManToMany = Command::confirm("Does it has another <fg=red>many to many</fg=red> relation ? ", false);
        }

        return $relations;
    }

    /**
     * get model attributes from the user using the command line
     * @return array
     */
    public function getModelAttributesFromPrompts(): array
    {
        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        while (empty(trim($paramsString))) {
            $this->error('Invalid Input');
            $paramsString = $this->ask('Enter your params like "name,started_at,..."');
        }

        $paramsString = explode(',', $paramsString);
        $fieldsWithDataType = [];
        foreach ($paramsString as $field) {
            $field = columnNaming($field);
            $type = $this->choice(
                "What is the data type of the (( {$field} field )) ? default is ",
                $this->types,
                6,
            );
            $fieldsWithDataType[$field] = $type;
        }

        return $fieldsWithDataType;
    }

    /**
     * ask the user about the actor of the created model endpoints
     * @return array|string|null
     */
    public function checkTheActorUsingPrompts(): array|string|null
    {
        if (file_exists(base_path('app/Enums/RolesPermissionEnum.php'))) {
            if (class_exists('\App\Enums\RolesPermissionEnum')) {
                /** @noinspection PhpFullyQualifiedNameUsageInspection */
                $roles = \App\Enums\RolesPermissionEnum::ALLROLES;
                $roles[] = 'none';
                return $this->choice('Who Is The Actor Of this Endpoint ? ', $roles, 'none');
            }
        }

        return 'none';
    }
}
