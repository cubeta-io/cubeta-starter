<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

abstract class ClassStubBuilder extends StubBuilder
{
    protected string $namespace;
    protected array $imports = [];
    protected array $traits = [];
    protected array $properties = [];
    protected array $methods = [];
    protected array $dockBlock = [];

    public function import(string|array $import): static
    {
        if (is_array($import)) {
            $this->imports = array_merge($import, $this->imports);
        } else {
            $this->imports[] = $import;
        }

        return $this;
    }

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

    public function method(string|array $method): static
    {
        if (is_array($method)) {
            $this->methods = array_merge($method, $this->methods);
        } else {
            $this->methods[] = $method;
        }

        return $this;
    }

    public function dockBlock(string|array $dockBlock): static
    {
        if (is_array($dockBlock)) {
            $this->dockBlock = array_merge($dockBlock, $this->dockBlock);
        } else {
            $this->dockBlock[] = $dockBlock;
        }

        return $this;
    }

    public function namespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }
}