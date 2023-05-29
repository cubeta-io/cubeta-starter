<?php

namespace Cubeta\CubetaStarter\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface IBaseRepository
 */
interface IBaseRepository
{
    /**
     * @param array $relationships
     * @return mixed
     */
    public function all(array $relationships = []): mixed;


    /**
     * @param array $relationships
     * @param int $per_page
     * @return array|null
     */
    public function all_with_pagination(array $relationships = [], int $per_page = 10): ?array;

    /**
     * @param array $data
     * @param array $relationships
     * @return mixed
     */
    public function create(array $data, array $relationships = []): mixed;


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


    /**
     * @param $id
     * @param array $relationships
     * @return Model|Collection|Builder|array|null
     */
    public function find($id, array $relationships = []): Model|Collection|Builder|array|null;

    /**
     * @param $data
     * @return array
     */
    public function formatPaginateData($data): array;
}
