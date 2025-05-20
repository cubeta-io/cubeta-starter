<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Routes;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;
use Illuminate\Support\Arr;

class RoutesFileStubBuilder extends PhpFileStubBuilder
{
    private array $routes = [];

    /**
     * @param string|string[] $route
     * @return $this
     */
    public function route(string|array $route): static
    {
        $this->routes = array_merge($this->routes, Arr::wrap($route));
        $this->routes = array_map(function (string $route) {
            if (!str_ends_with($route, ";")) {
                return "$route;";
            }
            return $route;
        }, $this->routes);
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Routes/RoutesFile.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            ...$this->stubProperties,
            '{{routes}}' => implode(PHP_EOL, $this->routes),
        ];
    }
}