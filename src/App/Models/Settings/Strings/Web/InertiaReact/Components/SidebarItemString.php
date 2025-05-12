<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components;

class SidebarItemString
{
    private string $title;
    private string $routeName;

    /**
     * @param string $title
     * @param string $routeName
     */
    public function __construct(string $title, string $routeName)
    {
        $this->title = $title;
        $this->routeName = $routeName;
    }

    public function __toString(): string
    {
        return "{title:$this->title , href:route(\"$this->routeName\") , icon: () => <TableCells />}";
    }
}