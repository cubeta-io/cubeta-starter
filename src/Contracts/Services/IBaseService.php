<?php

namespace Cubeta\CubetaStarter\Contracts\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface IBaseService
{
    public function index(array $relations = []): mixed;

    public function indexWithPagination(array $relations = [], int $per_page = 10): ?array;

    public function formatPaginationData($data): array;

    public function store(array $data, array $relationships = []): mixed;

    public function view($id, array $relationships = []): Model|Collection|Builder|array|null;

    public function update(array $data, $id, array $relationships = []): mixed;

    public function delete($id): ?bool;
}
