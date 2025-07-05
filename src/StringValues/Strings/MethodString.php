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

    /**
     * @var PhpImportString[]
     */
    public array $imports = [];

    /**
     * @var DocBlockPropertyString[]
     */
    public array $docBlocs = [];

    /**
     * @param string                   $name
     * @param array                    $parameters
     * @param array|string             $body
     * @param string                   $visibility
     * @param string|null              $returnType
     * @param PhpImportString[]        $imports
     * @param DocBlockPropertyString[] $docBlocs
     */
    public function __construct(string $name, array $parameters, array|string $body, string $visibility = 'public', ?string $returnType = null, array $imports = [], array $docBlocs = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->body = Arr::wrap($body);
        $this->visibility = $visibility;
        $this->returnType = $returnType;
        $this->imports = $imports;
        $this->docBlocs = $docBlocs;

        if (count($this->docBlocs) > 0) {
            foreach ($this->docBlocs as $docBloc) {
                $this->imports = array_merge($this->imports, $docBloc->imports);
            }
        }
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

        $comment = "";

        if (count($this->docBlocs) > 0) {
            $comment = "/**\n" . implode("\n * ", $this->docBlocs) . "\n*/";
        }

        if ($this->returnType) {
            return "{$comment}\n{$this->visibility} function $this->name($parameters): {$this->returnType}{{$body}}";
        }

        return "{$comment}\n{$this->visibility} function $this->name($parameters) {{$body}}";
    }
}