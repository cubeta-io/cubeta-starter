<?php

namespace Cubeta\CubetaStarter\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface IBaseRepository
 */
interface IBaseRepository
{
    public function all(array $relationships = []): mixed;

    public function all_with_pagination(array $relationships = [], int $per_page = 10): ?array;

    public function create(array $data, array $relationships = []): mixed;

    public function update(array $data, $id, array $relationships = []): mixed;

    public function delete($id): ?bool;

    public function find($id, array $relationships = []): Model|Collection|Builder|array|null;

    public function formatPaginateData($data): array;
}
