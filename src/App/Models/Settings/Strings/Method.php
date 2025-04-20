<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

use Illuminate\Support\Arr;

class Method
{
    private string $name;
    private string $visibility = 'public';
    private array $parameters = [];
    private array $body;

    public function __construct(string $name, array $parameters, array|string $body, string $visibility = 'public')
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->body = Arr::wrap($body);
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

        return "$this->visibility function $this->name($parameters)
                {
                    $body
                }";
    }
}