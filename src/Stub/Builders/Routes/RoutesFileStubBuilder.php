<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Routes;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Stub\Contracts\PhpFileStubBuilder;
use Illuminate\Support\Arr;

class RoutesFileStubBuilder extends PhpFileStubBuilder
{
    private array $routes = [];

    public function init(): void
    {
        $this->routes = [];
        $this->stubProperties = [];
    }

    /**
     * @param Routes|Routes[] $route
     * @return $this
     */
    public function route(Routes|array $route): static
    {
        $this->routes = array_merge($this->routes, Arr::wrap($route));
        $this->routes = array_map(function (Routes $route) {
            return $route->toString();
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