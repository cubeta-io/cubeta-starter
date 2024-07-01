<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as RegularCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @template T of Model
 */
interface IBaseRepository
{
    public static function make(): static;

    /**
     * @param array $relations
     * @return Builder<T>
     */
    public function globalQuery(array $relations = []): Builder;

    /**
     * @param array $relationships
     * @param int $per_page
     * @return array{data:Collection<T>|array<T>|RegularCollection<T> , pagination_data:array}|null
     */
    public function all_with_pagination(array $relationships = [], int $per_page = 10): ?array;

    /**
     * @param $data
     * @return array
     */
    public function formatPaginateData($data): array;

    /**
     * @param array $data
     * @param array $relationships
     * @return T
     */
    public function create(array $data, array $relationships = []): Model;

    /**
     * @param $id
     * @return bool|null
     */
    public function delete($id): ?bool;

    /**
     * @param $id
     * @param array $relationships
     * @return T|null
     */
    public function find($id, array $relationships = []): ?Model;

    /**
     * @param array $data
     * @param $id
     * @param array $relationships
     * @return T|null
     */
    public function update(array $data, $id, array $relationships = []): ?Model;

    /**
     * @param array $ids
     * @return BinaryFileResponse
     */
    public function export(array $ids = []): BinaryFileResponse;

    /**
     * @return BinaryFileResponse
     */
    public function getImportExample(): BinaryFileResponse;

    /**
     * @return void
     */
    public function import(): void;
}
