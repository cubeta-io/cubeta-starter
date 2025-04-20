<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
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

        return $this;
    }

    public function property(string|array $property): static
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

    public function dockBlock(string $property, string $type): static
    {
        $this->dockBlock[$property] = $type;
        return $this;
    }

    public function namespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    protected function getStubPropertyArray(): array
    {
        $traits = "";
        foreach ($this->traits as $trait) {
            $traits .= "use {$trait};\n";
        }

        $docBlocks = "/**\n";
        foreach ($this->dockBlock as $property => $type) {
            $docBlocks .= "* @property $type $property\n";
        }
        $docBlocks .= "*/\n";

        return [
            '{{namespace}}' => $this->namespace,
            '{{traits}}' => $traits,
            '{{properties}}' => implode("\n", $this->properties),
            '{{methods}}' => array_reduce($this->methods, fn($carry, $method) => "$carry\n\n$method"),
            '{{doc_block}}' => $docBlocks,
            ...$this->stubProperties,
        ];
    }
}