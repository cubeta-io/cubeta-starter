<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;

class MigrationColumnString
{
    public ?ImportString $import;
    public string $name;
    public string $method;
    public bool $nullable;
    public bool $unique;
    public bool $isKey;

    public function __construct(string $name, string $method, bool $nullable, bool $unique, bool $isKey = false, ?ImportString $import = null)
    {
        $this->import = $import;
        $this->name = $name;
        $this->method = $method;
        $this->nullable = $nullable;
        $this->unique = $unique;
        $this->isKey = $isKey;
    }

    public function __toString(): string
    {
        $statement = "\$table->{$this->method}(";
        if ($this->isKey) {
            $statement .= "$this->name";
        } else {
            $statement .= "'$this->name'";
        }

        $statement .= ")";

        if ($this->nullable) {
            $statement .= "->nullable()";
        }

        if ($this->unique) {
            $statement .= "->unique()";
        }

        if ($this->isKey) {
            $statement .= "->constrained()->cascadeOnDelete()";
        }

        return "$statement;";
    }
}