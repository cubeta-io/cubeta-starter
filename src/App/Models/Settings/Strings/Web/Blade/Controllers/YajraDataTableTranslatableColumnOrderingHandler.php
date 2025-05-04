<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Controllers;

class YajraDataTableTranslatableColumnOrderingHandler
{
    /**
     * @var TranslatableColumnDataTableColumnOrderingOptionsArrayString[]
     */
    private array $config = [];

    /**
     * @param TranslatableColumnDataTableColumnOrderingOptionsArrayString[] $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __toString(): string
    {
        if (count($this->config) == 0) {
            $columns = "";
        } else {
            $columns = implode(",\n", $this->config);
        }
        return "\$query = \$this->orderTranslatableColumns(\$query, [\n$columns\n]);";
    }
}