<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\IBaseRepository;

abstract class BaseService implements IBaseService
{
    protected IBaseRepository $repository;

    public function __construct(IBaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function delete($id): ?bool
    {
        return $this->repository->delete($id);
    }

    public function formatPaginationData($data): array
    {
        return $this->repository->formatPaginateData($data);
    }

    public function index(array $relations = []): mixed
    {
        return $this->repository->all($relations);
    }

    public function indexWithPagination(array $relations = [], int $per_page = 10): ?array
    {
        return $this->repository->all_with_pagination($relations, $per_page);
    }

    public function store(array $data, array $relationships = []): mixed
    {
        return $this->repository->create($data, $relationships);
    }

    public function update(array $data, $id, array $relationships = []): mixed
    {
        return $this->repository->update($data, $id, $relationships);
    }

    public function view($id, array $relationships = []): Model|Collection|Builder|array|null
    {
        return $this->repository->find($id, $relationships);
    }
}
