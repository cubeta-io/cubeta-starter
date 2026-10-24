<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components;

class SidebarItemString
{
    private string $routeName;

    private string $title;

    public function __construct(string $title, string $routeName)
    {
        $this->title = $title;
        $this->routeName = $routeName;
    }

    public function __toString(): string
    {
        return "{title:t(\"models.{$this->title}.plural\") , href:route(\"$this->routeName\") , icon:  <TableIcon />}";
    }
}
