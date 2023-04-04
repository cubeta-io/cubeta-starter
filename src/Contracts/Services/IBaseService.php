<?php

namespace Cubeta\CubetaStarter\Contracts\Services;

interface IBaseService
{
    public function index(array $relations = []);

    public function indexWithPagination(array $relations = [], $per_page = 10);

    public function formatPaginationData($data);

    public function store(array $data, array $relations = []);

    public function view($id, array $relations = []);

    public function update(array $data, $id, array $relations = []);

    public function delete($id);
}
