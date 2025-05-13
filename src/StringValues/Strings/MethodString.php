<?php

namespace Cubeta\CubetaStarter\StringValues\Strings;

use Illuminate\Support\Arr;

class MethodString
{
    public string $name;
    public string $visibility;
    public array $parameters;
    public array $body;
    public ?string $returnType;
    public array $imports = [];

    /**
     * @param string            $name
     * @param array             $parameters
     * @param array|string      $body
     * @param string            $visibility
     * @param string|null       $returnType
     * @param PhpImportString[] $imports
     */
    public function __construct(string $name, array $parameters, array|string $body, string $visibility = 'public', ?string $returnType = null, array $imports = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->body = Arr::wrap($body);
        $this->visibility = $visibility;
        $this->returnType = $returnType;
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        $parameters = "";
        foreach ($this->parameters as $name => $type) {
            $name = trim($name);
            $type = trim($type);
            $parameters .= "$type \$$name, ";
        }

        $body = array_reduce($this->body, function ($carry, $item) {
            if (!str_ends_with($item, ";")) {
                return "$carry\n$item;";
            }

            return "$carry\n$item";
        });

        if ($this->returnType) {
            return "$this->visibility function $this->name($parameters): {$this->returnType}{{$body}}";
        }

        return "$this->visibility function $this->name($parameters) {{$body}}";
    }
}