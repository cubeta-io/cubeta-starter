<?php

namespace App\Services\Contracts;

use App\Repositories\Contracts\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as RegularCollection;

/**
 * @template T of Model
 * @implements IBaseService<T>
 */
abstract class BaseService implements IBaseService
{
    protected IBaseRepository $repository;

    public function __construct(IBaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param            $id
     * @return bool|null
     */
    public function delete($id): ?bool
    {
        return $this->repository->delete($id);
    }

    /**
     * @param        $data
     * @return array
     */
    public function formatPaginationData($data): array
    {
        return $this->repository->formatPaginateData($data);
    }

    /**
     * @param array $relations
     * @return Collection<T>|RegularCollection<T>|array<T>
     */
    public function index(array $relations = []): Collection|array|RegularCollection
    {
        return $this->repository->all($relations);
    }

    /**
     * @param array $relations
     * @param int $per_page
     * @return array{data:Collection<T>|array<T>|RegularCollection<T> , pagination_data:array}|null
     */
    public function indexWithPagination(array $relations = [], int $per_page = 10): ?array
    {
        return $this->repository->all_with_pagination($relations, $per_page);
    }

    /**
     * @param array $data
     * @param array $relationships
     * @return T|null
     */
    public function store(array $data, array $relationships = []): Model
    {
        return $this->repository->create($data, $relationships);
    }

    /**
     * @param array $data
     * @param        $id
     * @param array $relationships
     * @return T|null
     */
    public function update(array $data, $id, array $relationships = []): ?Model
    {
        return $this->repository->update($data, $id, $relationships);
    }

    /**
     * @param $id
     * @param array $relationships
     * @return T|null
     */
    public function view($id, array $relationships = []): ?Model
    {
        return $this->repository->find($id, $relationships);
    }

    /**\
     * @param array $ids
     * @return string
     */
    public function export(array $ids = []): string
    {
        return $this->repository->export($ids);
    }
}
