<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact;

class TsImportString
{
    public ?string $import;
    public bool $default = true;
    public string $from;

    /**
     * @param string|null $import
     * @param bool        $default
     * @param string      $from
     */
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
        } elseif ($this->import && !$this->default) {
            return "import {{$this->import}} from \"$this->from\";";
        }

        return "import \"$this->from\";";
    }
}