<?php

namespace Cubeta\CubetaStarter\Contracts\Repositories;

/**
 * Interface IBaseRepository
 */
interface IBaseRepository
{
    /**
     * @return mixed
     */
    public function all(array $relationships = []);

    /**
     * @param  int  $per_page
     * @return mixed
     */
    public function all_with_pagination(array $relationships = [], $per_page = 10);

    /**
     * @return mixed
     */
    public function create(array $data, array $relationships = []);

    /**
     * @param  int  $id
     * @return mixed
     */
    public function update(array $data, $id, array $relationships = []);

    /**
     * @return mixed
     */
    public function delete($id);

    /**
     * @return mixed
     */
    public function find($id, array $relationships = []);

    /**
     * format pagination data
     *
     * @return mixed
     */
    public function formatPaginateData($data);
}
