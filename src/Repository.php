<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface Repository
{
    /**
     * Fin an item by id
     *
     * @param  mixed  $id
     * @param  array  $relations array of relations
     * @return Model|null
     */
    public function find($id, array $relations = []);

    /**
     * find or fail
     *
     * @param  mixed  $id
     * @param  array  $relations array of relations
     * @return mixed
     */
    public function findOrFail($id, array $relations = []);

    /**
     * Return all items
     *
     * @return Collection|null
     */
    public function all(array $relations = []);

    /**
     * get all with pagination
     *
     * @param  int  $per_page
     * @return mixed
     */
    public function allPaginated(array $relations = [], $per_page = 10);

    /**
     * Return query builder
     *
     * @param  array  $relations array of relations
     * @return Builder|null
     */
    public function query(array $relations = []);

    /**
     * Create an item
     *
     * @param  bool  $to_compress
     * @param  bool  $is_base_64
     * @param  int  $width
     * @return Model|null
     */
    public function create(array $data, array $filesKeys = [], $to_compress = true, $is_base_64 = false, $width = 300);

    /**
     * Update a model
     *
     * @param  int|mixed  $id
     * @return bool|mixed
     */
    public function update($id, array $data, array $filesKeys = [], bool $to_compress = true, bool $is_base_64 = false, int $width = 300);

    /**
     * Delete a model
     *
     * @param  int|Model  $id
     */
    public function delete($id);

    /**
     * multiple delete
     *
     * @return mixed
     */
    public function destroy(array $ids);

    /**
     * update or create item
     *
     * @return mixed
     */
    public function updateOrCreate(array $conditions, array $columns = []);
}
