<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;

class CubeKey extends CubeAttribute implements HasFakeMethod
{

    public function fakeMethod(): FakeMethodString
    {
        $relatedModel = CubeTable::create(str_replace('_id', '', $this->name));

        return new FakeMethodString(
            $this->name,
            "{$relatedModel->modelName}::factory()",
            new ImportString($relatedModel->getModelNameSpace(false))
        );
    }
}