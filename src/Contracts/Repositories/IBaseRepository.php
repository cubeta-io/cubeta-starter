<?php

namespace cubeta\CubetaStarter\Contracts\Repositories;

/**
 * Interface IBaseRepository
 */
interface IBaseRepository
{
    /**
     * @param  array  $relationships
     * @return mixed
     */
    public function all(array $relationships = []);

    /**
     * @param  array  $relationships
     * @param  int  $per_page
     * @return mixed
     */
    public function all_with_pagination(array $relationships = [], $per_page = 10);

    /**
     * @param  array  $data
     * @param  array  $relationships
     * @return mixed
     */
    public function create(array $data, array $relationships = []);

    /**
     * @param  array  $data
     * @param  int  $id
     * @param  array  $relationships
     * @return mixed
     */
    public function update(array $data, $id, array $relationships = []);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $id
     * @param  array  $relationships
     * @return mixed
     */
    public function find($id, array $relationships = []);

    /**
     * format pagination data
     *
     * @param $data
     * @return mixed
     */
    public function formatPaginateData($data);
}
