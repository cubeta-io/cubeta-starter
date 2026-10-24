<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Controllers;

class TranslatableColumnDataTableColumnOrderingOptionsArrayString
{
    private int $columnIndex;

    private string $columnName;

    private int $orderIndex;

    public function __construct(int $columnIndex, string $columnName, int $orderIndex = 0)
    {
        $this->orderIndex = $orderIndex;
        $this->columnIndex = $columnIndex;
        $this->columnName = $columnName;
    }

    public function __toString(): string
    {
        return "['orderIndex' => {$this->orderIndex}, 'columnIndex' => {$this->columnIndex}, 'columnName' => '{$this->columnName}']";
    }
}
