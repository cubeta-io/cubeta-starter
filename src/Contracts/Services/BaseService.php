<?php

namespace Cubeta\CubetaStarter\Contracts\Services;

use Cubeta\CubetaStarter\Contracts\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService implements IBaseService
{
    protected IBaseRepository $repository;

    public function __construct(IBaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $relations
     * @return mixed
     */
    public function index(array $relations = []): mixed
    {
        return $this->repository->all($relations);
    }

    /**
     * @param array $relations
     * @param int $per_page
     * @return array|null
     */
    public function indexWithPagination(array $relations = [], int $per_page = 10): ?array
    {
        return $this->repository->all_with_pagination($relations, $per_page);
    }

    /**
     * @param $data
     * @return array
     */
    public function formatPaginationData($data): array
    {
        return $this->repository->formatPaginateData($data);
    }

    /**
     * @param array $data
     * @param array $relationships
     * @return mixed
     */
    public function store(array $data, array $relationships = []): mixed
    {
        return $this->repository->create($data, $relationships);
    }

    /**
     * @param $id
     * @param array $relationships
     * @return array|Builder|Collection|Model|null
     */
    public function view($id, array $relationships = []): Model|Collection|Builder|array|null
    {
        return $this->repository->find($id, $relationships);
    }

    /**
     * @param array $data
     * @param $id
     * @param array $relationships
     * @return mixed
     */
    public function update(array $data, $id, array $relationships = []): mixed
    {
        return $this->repository->update($data, $id, $relationships);
    }

    /**
     * @param $id
     * @return bool|null
     */
    public function delete($id): ?bool
    {
        return $this->repository->delete($id);
    }
}
