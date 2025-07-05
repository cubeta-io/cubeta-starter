<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Modules\Views;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Illuminate\Support\Collection;

/**
 * @mixin CubeTable
 */
trait HasViewsAndRoutes
{
    public function resourceRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::resource($this, $container, $actor);
    }

    public function indexRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::index($this, $container, $actor);
    }

    public function showRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::show($this, $container, $actor);
    }

    public function createRoute(?string $actor = null): Routes
    {
        return Routes::create($this, $actor);
    }

    public function storeRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::store($this, $container, $actor);
    }

    public function editRoute(?string $actor = null): Routes
    {
        return Routes::edit($this, $actor);
    }

    public function updateRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::update($this, $container, $actor);
    }

    public function deleteRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::delete($this, $container, $actor);
    }

    public function dataRoute(?string $actor = null): Routes
    {
        return Routes::data($this, $actor);
    }

    public function allPaginatedJson(?string $actor = null): Routes
    {
        return Routes::allPaginatedJson($this, $actor);
    }

    public function exportRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::export($this, $container, $actor);
    }

    public function importRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::import($this, $container, $actor);
    }

    public function importExampleRoute(?string $actor = null, string $container = ContainerType::API): Routes
    {
        return Routes::importExample($this, $container, $actor);
    }

    /**
     * @param string|null $actor
     * @param string      $container
     * @return Routes[]|Collection<Routes>
     */
    public function crudRoutes(?string $actor = null, string $container = ContainerType::API): array|Collection
    {
        return Routes::crudRoutes($this, $container, $actor);
    }

    public function indexView(?string $actor = null): Views
    {
        return Views::index($this, $actor);
    }

    public function showView(?string $actor = null): Views
    {
        return Views::show($this, $actor);
    }

    public function createView(?string $actor = null): Views
    {
        return Views::create($this, $actor);
    }

    public function editView(?string $actor = null): Views
    {
        return Views::edit($this, $actor);
    }
}