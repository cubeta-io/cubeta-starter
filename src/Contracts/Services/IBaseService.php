<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface IBaseService
{
    public function delete($id): ?bool;

    public function formatPaginationData($data): array;

    public function index(array $relations = []): mixed;

    public function indexWithPagination(array $relations = [], int $per_page = 10): ?array;

    public function store(array $data, array $relationships = []): mixed;

    public function update(array $data, $id, array $relationships = []): mixed;

    public function view($id, array $relationships = []): Model|Collection|Builder|array|null;
}
