<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components;

use Stringable;

class SidebarItemString
{
    private string $modelName;
    private string $routeName;

    public function __construct(string $modelName , string $routeName)
    {
        $this->modelName = $modelName;
        $this->routeName = $routeName;
    }

    public function __toString(): string
    {
        return "<li class=\"nav-item\">
                    <a class=\"nav-link collapsed @if(str_contains(request()->fullUrl() , route('{$this->routeName}'))) active-sidebar-item @endif\" href=\"{{route('{$this->routeName}')}}\">
                        <i class=\"bi bi-circle\"/>
                        <span>{$this->modelName}</span>
                    </a>
               </li>";
    }
}