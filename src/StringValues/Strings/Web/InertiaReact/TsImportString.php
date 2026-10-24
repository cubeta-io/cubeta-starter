<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact;

class TsImportString
{
    public bool $default = true;

    public string $from;

    public ?string $import;

    public function __construct(?string $import, string $from, bool $default = true)
    {
        $this->import = $import;
        $this->default = $default;
        $this->from = $from;
    }

    public function __toString(): string
    {
        if ($this->default && $this->import) {
            return "import $this->import from \"$this->from\";";
        } elseif ($this->import && ! $this->default) {
            return "import {{$this->import}} from \"$this->from\";";
        }

        return "import \"$this->from\";";
    }
}
