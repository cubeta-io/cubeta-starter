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

        $imports = "";
        foreach ($this->imports as $import) {
            $imports .= "use {$import};\n";
        }

        $docBlocks = "/**\n";
        foreach ($this->dockBlock as $property => $type) {
            $docBlocks .= "* @property $type $property\n";
        }
        $docBlocks .= "*/\n";

        return [
            '{{namespace}}' => $this->namespace,
            '{{imports}}' => $imports,
            '{{traits}}' => $traits,
            '{{properties}}' => implode("\n", $this->properties),
            '{{methods}}' => implode("\n", $this->methods),
            '{{doc_block}}' => $docBlocks,
            ...$this->stubProperties,
        ];
    }
}