<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Method;

abstract class ClassStubBuilder extends StubBuilder
{
    protected string $namespace;
    protected array $traits = [];
    protected array $properties = [];
    protected array $methods = [];
    protected array $dockBlock = [];

    public function trait(string|array $trait): static
    {
        if (is_array($trait)) {
            $this->traits = array_merge($trait, $this->traits);
        } else {
            $this->traits[] = $trait;
        }

        $this->traits = collect($this->traits)
            ->map(fn($trait) => trim($trait))
            ->unique()
            ->toArray();

        return $this;
    }

    /**
     * @param string|array $property
     * @return $this
     */
    public function property(array|string $property): static
    {
        if (is_array($property)) {
            $this->properties = array_merge($property, $this->properties);
        } else {
            $this->properties[] = $property;
        }

        return $this;
    }

    /**
     * @param string|array|Method|Method[] $method
     * @return $this
     */
    public function method(string|array|Method $method): static
    {
        if (is_array($method)) {
            $this->methods = array_merge($method, $this->methods);
        } else {
            $this->methods[] = $method;
        }

        return $this;
    }

    public function dockBlock(DocBlockProperty $property): static
    {
        $this->dockBlock[] = $property;
        if ($property->import) {
            $this->import($property->import);
        }

        $this->dockBlock = collect($this->dockBlock)
            ->map(fn(DocBlockProperty $property) => trim($property))
            ->unique()
            ->toArray();

        return $this;
    }

    public function namespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    protected function getStubPropertyArray(): array
    {
        return [
            '{{namespace}}' => $this->namespace,
            '{{traits}}' => implode("\n", array_map(fn($trait) => "use " . $trait . ";", $this->traits)),
            '{{properties}}' => implode("\n *", $this->properties),
            '{{methods}}' => array_reduce($this->methods, fn($carry, $method) => "$carry\n\n$method"),
            '{{doc_block}}' => implode("\n *", $this->dockBlock),
            ...$this->stubProperties,
        ];
    }
}