<?php

namespace Cubeta\CubetaStarter\Contracts\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface IBaseService
{
    /**
     * @param array $relations
     * @return mixed
     */
    public function index(array $relations = []): mixed;

    /**
     * @param array $relations
     * @param int $per_page
     * @return array|null
     */
    public function indexWithPagination(array $relations = [], int $per_page = 10): ?array;

    /**
     * @param $data
     * @return array
     */
    public function formatPaginationData($data): array;

    /**
     * @param array $data
     * @param array $relationships
     * @return mixed
     */
    public function store(array $data, array $relationships = []): mixed;

    /**
     * @param $id
     * @param array $relationships
     * @return Model|Collection|Builder|array|null
     */
    public function view($id, array $relationships = []): Model|Collection|Builder|array|null;

    /**
     * @param array $data
     * @param $id
     * @param array $relationships
     * @return mixed
     */
    public function update(array $data, $id, array $relationships = []): mixed;

    /**
     * @param $id
     * @return bool|null
     */
    public function delete($id): ?bool;
}
