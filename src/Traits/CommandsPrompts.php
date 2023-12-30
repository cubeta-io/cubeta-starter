<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Console\Command;

trait CommandsPrompts
{
    use SettingsHandler;

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

            $relations[$relatedModel] = RelationsTypeEnum::HasMany;

            $itHasMany = Command::confirm('Does it has another <fg=red>has many</fg=red> relation ? ', false);
        }

        $itManToMany = Command::confirm("Does this model related with another model by <fg=red>many to many</fg=red> relation ?", false);

        while ($itManToMany) {
            $relatedModel = Command::anticipate("What is the name of the related model table ? ", $createdModels);

            while (empty(trim($relatedModel))) {
                Command::error('Invalid Input');
                $relatedModel = Command::anticipate('What is the name of the related model table ?', $createdModels);
            }

            $relations[$relatedModel] = RelationsTypeEnum::ManyToMany;

            $itManToMany = Command::confirm("Does it has another <fg=red>many to many</fg=red> relation ? ", false);
        }

        return $relations;
    }
}
