<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as RegularCollection;

/**
 * @template T of Model
 */
interface IBaseService
{
    /**
     * @param            $id
     * @return bool|null
     */
    public function delete($id): ?bool;

    /**
     * @param        $data
     * @return array
     */
    public function formatPaginationData($data): array;

    /**
     * @param  array                                    $relations
     * @return Collection<T>|RegularCollection<T>|array<T>
     */
    public function index(array $relations = []): RegularCollection|Collection|array;

    /**
     * @param  array                                                                             $relations
     * @param  int                                                                               $per_page
     * @return array{data:Collection<T>|array<T>|RegularCollection<T> , pagination_data:array}|null
     */
    public function indexWithPagination(array $relations = [], int $per_page = 10): ?array;

    /**
     * @param  array  $data
     * @param  array  $relationships
     * @return T|null
     */
    public function store(array $data, array $relationships = []): ?Model;

    /**
     * @param  array $data
     * @param        $id
     * @param  array $relationships
     * @return T
     */
    public function update(array $data, $id, array $relationships = []): ?Model;

    /**
     * @param $id
     * @param array $relationships
     * @return T|null
     */
    public function view($id, array $relationships = []): ?Model;
}
