<?php

namespace cubeta\CubetaStarter\Contracts\Services;


use cubeta\CubetaStarter\Contracts\Repositories\IBaseRepository;

abstract class BaseService implements IBaseService
{
    protected $repository;

    public function __construct(IBaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(array $relations = [])
    {
        return $this->repository->all($relations);
    }

    public function indexWithPagination(array $relations = [], $per_page = 10)
    {
        return $this->repository->all_with_pagination($relations, $per_page);
    }

    public function formatPaginationData($data)
    {
        return $this->repository->formatPaginateData($data);
    }

    public function store(array $data, array $relation = [])
    {
        return $this->repository->create($data, $relation);
    }

    public function view($id, array $relations = [])
    {
        return $this->repository->find($id, $relations);
    }

    public function update(array $data, $id, array $relation = [])
    {
        return $this->repository->update($data, $id, $relation);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
