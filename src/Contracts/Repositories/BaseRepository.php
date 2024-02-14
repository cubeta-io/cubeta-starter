<?php

namespace App\Repositories\Contracts;

use App\Traits\FileHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection as RegularCollection;
use Illuminate\Support\Facades\Schema;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class BaseRepository
 */
abstract class BaseRepository implements IBaseRepository
{
    use FileHandler;

    /**
     * @template T of Model<T>
     * @var T
     */
    protected Model $model;
    private Filesystem $fileSystem;
    private array $filterKeys = [];
    private array $fileColumnsName = [];
    private array $modelTableColumns = [];
    private array $orderableKeys = [];
    private array $relationSearchableKeys = [];
    private array $searchableKeys = [];

    /**
     * BaseRepository Constructor
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        if (method_exists($this->model, 'filesKeys')) {
            $this->fileColumnsName = $this->model->filesKeys();
        }

        if (method_exists($this->model, 'searchableArray')) {
            $this->searchableKeys = $this->model->searchableArray();
        }

        if (method_exists($this->model, 'relationsSearchableArray')) {
            $this->relationSearchableKeys = $this->model->relationsSearchableArray();
        }

        if (method_exists($this->model, 'filterArray')) {
            $this->filterKeys = $this->model->filterArray();
        }

        $this->modelTableColumns = $this->getTableColumns();
    }

    public function getTableColumns(): array
    {
        $table = $this->model->getTable();

        return Schema::getColumnListing($table);
    }

    /**
     * @template T of Model<T>
     * @param array $relationships
     * @return Collection<T>|RegularCollection<T>|array
     */
    public function all(array $relationships = []): Collection|array|RegularCollection
    {
        return $this->globalQuery($relationships)->get();
    }

    public function globalQuery(array $relations = []): Builder
    {
        $query = $this->model->with($relations);

        if (request()->method() == 'GET') {
            $query = $this->addSearch($query);
            $query = $this->filterFields($query);
            $query = $this->orderQueryBy($query);
        }

        return $query;
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     */

    private function addSearch($query): mixed
    {
        if (request()->has('search')) {
            $keyword = request()->search;

            if (count($this->searchableKeys) > 0) {
                foreach ($this->searchableKeys as $search_attribute) {
                    $query->orWhere($search_attribute, 'REGEXP', "(?i).*{$keyword}.*");
                }
            }

            if (count($this->relationSearchableKeys) > 0) {

                foreach ($this->relationSearchableKeys as $relation => $values) {

                    foreach ($values as $key => $search_attribute) {
                        $query->orWhereHas($relation, function ($q) use ($keyword, $search_attribute) {
                            $q->where($search_attribute, 'REGEXP', "(?i).*{$keyword}.*");
                        });
                    }
                }
            }
            $query->orWhere('id', $keyword);
        }

        return $query;
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     */

    /**
     * this function implement already defined filters in the model
     * @param Builder $query
     * @return Builder
     */
    private function filterFields(Builder $query): Builder
    {
        foreach ($this->filterKeys as $filterFields) {
            $field = $filterFields['field'] ?? $filterFields['name'];
            $operator = $filterFields['operator'] ?? "=";
            $relation = $filterFields['relation'] ?? null;
            $method = $filterFields['method'] ?? "where";
            $callback = $filterFields['query'] ?? null;
            $value = request($relation ?? $field);
            $range = is_array($value);

            if (!$value) {
                continue;
            }

            if ($relation) {
                $query = $query->whereHas($relation, function (QueryBuilder $q) use ($range, $field, $method, $operator, $value) {
                    if ($range) {
                        return $q->whereBetween($field, $value);
                    }
                    if ($operator === "like") {
                        return $q->{$method}($field, $operator, "%" . $value . "%");
                    }
                    return $q->{$method}($field, $operator, $value);
                });
            } elseif ($callback && is_callable($callback)) {
                $query = call_user_func($callback, $query, $value);
            } else {
                if ($range) {
                    $query = $query->whereBetween($field, $value);
                } elseif ($operator == 'like') {
                    $query = $query->{$method}($field, $operator, "%" . $value . "%");
                } else $query = $query->{$method}($field, $operator, $value);
            }
        }

        return $query;
    }

    private function orderQueryBy($query): mixed
    {
        $sortColumns = request()->sort_col;
        if (isset($sortColumns)) {
            if (is_array(request()->sort_col)) {
                foreach ($sortColumns as $col => $dir) {
                    if (in_array($col, $this->modelTableColumns)) {
                        $query->orderBy($col, $dir);
                    }
                }
            } elseif (in_array(request()->sort_col, $this->modelTableColumns)) {
                $query->orderBy(request()->sort_col, request()->sort_dir ?? 'asc');
            }

            return $query;
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * @template T of Model<T>
     * @param array $relationships
     * @param int $per_page
     * @return array{data:Collection<T>|array|RegularCollection<T> , pagination_data:array}|null
     */
    public function all_with_pagination(array $relationships = [], int $per_page = 10): ?array
    {
        $all = $this->globalQuery($relationships)->paginate($per_page);
        if (count($all) > 0) {
            $pagination_data = $this->formatPaginateData($all);
            return ['data' => $all, 'pagination_data' => $pagination_data];
        }
        return null;
    }

    #[ArrayShape(['currentPage' => 'int', 'from' => 'int', 'to' => 'int', 'total' => 'int', 'per_page' => 'int'])]
    public function formatPaginateData($data): array
    {
        $paginated_arr = $data->toArray();

        return [
            'currentPage' => $paginated_arr['current_page'],
            'from' => $paginated_arr['from'],
            'to' => $paginated_arr['to'],
            'total' => $paginated_arr['total'],
            'per_page' => $paginated_arr['per_page'],
        ];
    }

    public function create(array $data, array $relationships = []): mixed
    {
        $received_data = $data;
        $fileCols = $this->fileColName($data);

        foreach ($fileCols as $col) {
            $path = $this->storeUpdateFileIfExist($col, $data);
            if ($path != '') {
                $data["$col"] = $path;
            }
        }

        $result = $this->model->create($data);
        $result->refresh();

        return $result->load($relationships);
    }

    private function fileColName($data): array
    {
        $cols = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fileColumnsName)) {
                $cols[] = $key;
            }
        }

        return $cols;
    }

    private function storeUpdateFileIfExist(string $col_name, &$data, bool $is_store = true, $item = null): string
    {
        $image = '';
        if ($col_name != '') {
            if (array_key_exists($col_name, $data)) {
                $this->fileSystem = new Filesystem();
                if ($is_store) {
                    $this->makeDirectory(storage_path('app/public/' . $this->model->getTable()));
                    $image = $this->storeFile($data["{$col_name}"], $this->model->getTable());
                } else {
                    if ($item->{"{$col_name}"}) {
                        $image = $this->updateFile($data["{$col_name}"], $item->{"{$col_name}"}, $this->model->getTable());
                    } else {
                        $image = $this->storeFile($data["{$col_name}"], $this->model->getTable());
                    }
                }
                unset($data["{$col_name}"]);
            }

            return $image;
        }

        return '';
    }

    private function makeDirectory($path): mixed
    {
        $this->fileSystem->makeDirectory($path, 0777, true, true);

        return $path;
    }

    public function delete($id): ?bool
    {
        $result = $this->model->where('id', '=', $id)->first();
        if ($result) {
            $result->delete();

            return true;
        }

        return null;
    }

    /**
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function find($id, array $relationships = []): Model|Collection|Builder|array|null
    {
        $result = $this->model->with($relationships)->find($id);

        if ($result) {
            return $result;
        }

        return null;
    }

    /**
     * @return mixed|null
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public function update(array $data, $id, array $relationships = []): mixed
    {
        $item = $this->model->where('id', '=', $id)->first();

        if ($item) {
            $fileCols = $this->fileColName($data);
            foreach ($fileCols as $col) {
                $path = $this->storeUpdateFileIfExist($col, $data, false, $item);
                if ($path != '') {
                    $data["$col"] = $path;
                }
            }

            $item->fill($data);
            $item->save();

            return $item->load($relationships);
        }

        return null;
    }

    private function addFileToModel($file, $model, $data, $col_name): mixed
    {
        if ($col_name != '') {
            if (array_key_exists($col_name, $data)) {
                $model->{"{$col_name}"} = $file;
                $model->save();
            }
        }

        return $model;
    }
}
