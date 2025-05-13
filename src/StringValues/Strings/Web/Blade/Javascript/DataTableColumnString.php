<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript;

class DataTableColumnString
{
    private string $name;
    private ?string $render = null;
    private bool $sortable = true;
    private bool $searchable = true;

    /**
     * @param string      $name
     * @param string|null $render
     * @param bool        $sortable
     * @param bool        $searchable
     */
    public function __construct(string $name, string $render = null, bool $sortable = true, bool $searchable = true)
    {
        $this->name = $name;
        $this->render = $render;
        $this->sortable = $sortable;
        $this->searchable = $searchable;
    }

    public function __toString(): string
    {
        $object = "{data : \"$this->name\",";
        if ($this->searchable){
            $object .= "searchable : true,";
        }

        if ($this->sortable){
            $object .= "orderable : true,";
        }

        if ($this->render){
            $object .= "render : (data) => {{$this->render}},";
        }

        $object .= "},";
        return $object;
    }
}