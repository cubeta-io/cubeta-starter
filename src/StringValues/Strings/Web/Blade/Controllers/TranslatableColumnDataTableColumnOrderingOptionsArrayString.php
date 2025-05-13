<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Controllers;

class TranslatableColumnDataTableColumnOrderingOptionsArrayString
{
    private int $orderIndex;
    private int $columnIndex;
    private string $columnName;

    /**
     * @param int    $columnIndex
     * @param string $columnName
     * @param int    $orderIndex
     */
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