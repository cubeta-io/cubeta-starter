<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Resources;

use Illuminate\Support\Arr;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;

/**
 * @method self modelNamespace(string $namespace)
 */
class UserResourceStubBuilder extends ClassStubBuilder
{
    /** @var ResourcePropertyString[] */
    private array $additionalFields = [];

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{additional_fields}}' => implode(",\n", $this->additionalFields),
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Resources/UserResource.stub');
    }

    /**
     * @param  ResourcePropertyString|ResourcePropertyString[] $fields
     * @return $this
     */
    public function additionalFields(array|ResourcePropertyString $fields): static
    {
        $fields = Arr::wrap($fields);
        foreach ($fields as $item) {
            $this->import($item->imports);
        }

        $this->additionalFields = array_merge($fields, $this->additionalFields);

        return $this;
    }
}
