<?php

namespace Cubeta\CubetaStarter\Stub\Contracts;

use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\TraitString;

abstract class ClassStubBuilder extends StubBuilder
{
    protected string $namespace = "";
    protected array $traits = [];
    protected array $properties = [];
    protected array $methods = [];
    protected array $dockBlock = [];

    /**
     * @param TraitString|TraitString[] $trait
     * @return $this
     */
    public function trait(TraitString|array $trait): static
    {
        if (is_array($trait)) {
            foreach ($trait as $singleTrait) {
                if ($singleTrait->import) {
                    $this->import($singleTrait->import);
                }
            }

            $this->traits = array_merge($trait, $this->traits);
        } else {
            $this->traits[] = $trait;
            if ($trait->import) {
                $this->import($trait->import);
            }
        }

        $this->traits = collect($this->traits)
            ->unique(fn(TraitString $trait) => $trait->traitName)
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
     * @param MethodString|MethodString[] $method
     * @return $this
     */
    public function method(MethodString|array $method): static
    {
        if (is_array($method)) {
            foreach ($method as $item) {
                if ($item->imports) {
                    $this->import($item->imports);
                }
            }
            $this->methods = array_merge($method, $this->methods);
        } else {
            if ($method->imports) {
                $this->import($method->imports);
            }
            $this->methods[] = $method;
        }

        $this->methods = collect($this->methods)
            ->unique(function (MethodString $method) {
                return $method->name;
            })->toArray();

        return $this;
    }

    public function dockBlock(DocBlockPropertyString $property): static
    {
        $this->dockBlock[] = $property;
        if ($property->imports) {
            $this->import($property->imports);
        }

        $this->dockBlock = collect($this->dockBlock)
            ->unique(fn(DocBlockPropertyString $property) => $property->name)
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
        $methods = "";
        foreach ($this->methods as $method) {
            $methods .= $method . "\n\n";
        }

        return [
            '{{namespace}}' => $this->namespace,
            '{{traits}}' => array_reduce($this->traits, fn($carry, TraitString $trait) => "$carry\n$trait"),
            '{{methods}}' => $methods,
            '{{doc_block}}' => implode("\n * ", $this->dockBlock),
            ...$this->stubProperties,
        ];
    }
}