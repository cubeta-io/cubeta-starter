<?php

namespace Cubeta\CubetaStarter\Contracts\Repositories;

use Cubeta\CubetaStarter\Traits\FileHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class BaseRepository
 */
abstract class BaseRepository implements IBaseRepository
{
    use FileHandler;

    protected Model $model;

    private Filesystem $files;

    private array $fileColumnsName = [];

    private array $searchableKeys = [];

    private array $translatedSearchableKeys = [];

    private array $relationSearchableKeys = [];

    private array $orderableKeys = [];

    /**
     * BaseRepository Constructor
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        if (method_exists($this->model, 'filesKeys')) {
            $this->fileColumnsName = $this->model->filesKeys();
        }

        if (method_exists($this->model, 'orderableArray')) {
            $this->orderableKeys = $this->model->orderableArray();
        }

        if (method_exists($this->model, 'searchableArray')) {
            $this->searchableKeys = $this->model->searchableArray();
        }

        if (method_exists($this->model, 'translatedSearchableArray')) {
            $this->translatedSearchableKeys = $this->model->translatedSearchableArray();
        }

        if (method_exists($this->model, 'relationSearchableArray')) {
            $this->relationSearchableKeys = $this->model->relationSearchableArray();
        }
    }


    /**
     * @return array
     */
    public function getTableColumns(): array
    {
        $table = $this->model->getTable();
        return Schema::getColumnListing($table);
    }

    /**
     * @param array $relationships
     * @return mixed
     */
    public function all(array $relationships = []): mixed
    {
        $query = $this->model->with($relationships);
        $query = $this->add_search($query);
        $query = $this->orderQueryBy($query);
        return $query->get();
    }

    /**
     * @param $query
     * @return mixed
     */
    private function orderQueryBy($query): mixed
    {
        if (isset(request()->sort_col)) {
            if (in_array(request()->sort_col, $this->getTableColumns())) {
                $query->orderBy(request()->sort_col, request()->sort_dir ?? "asc");
            }
            return $query;
        }
        if (count($this->orderableKeys) > 0) {
            foreach ($this->orderableKeys as $colName => $direction) {
                $query->orderBy($colName, $direction);
            }
            return $query;
        }
        return $query->orderBy('created_at', 'desc');
    }


    /**
     * @param $query
     * @return mixed
     */
    private function add_search($query): mixed
    {
        if (request()->has('search')) {
            $keyword = request()->search;
            if (count($this->searchableKeys) > 0)
                foreach ($this->searchableKeys as $search_attribute) {
                    $query->orWhere($search_attribute, 'like', '%' . $keyword . '%');
                }
            if (count($this->translatedSearchableKeys) > 0)
                foreach ($this->translatedSearchableKeys as $search_attribute) {
                    $query->orWhere($search_attribute, "like", '%' . $keyword . '%');
                }
            if (count($this->relationSearchableKeys) > 0)
                foreach ($this->relationSearchableKeys as $relation => $values) {
                    foreach ($values as $key => $search_attribute) {
                        if (!($key === "translated")) {
                            $query->orWhereHas($relation, function ($q) use ($keyword, $search_attribute) {
                                $q->where($search_attribute, 'LIKE', '%' . $keyword . '%');
                            });
                        } else {
                            $query->orWhereHas($relation, function ($q) use ($keyword, $search_attribute) {
                                if (is_array($search_attribute)) {
                                    foreach ($search_attribute as $translate_attribute) {
                                        $q->orWhere($translate_attribute, 'like', '%' . $keyword . '%');
                                    }
                                } else {
                                    $q->orWhere($search_attribute, 'like', '%' . $keyword . '%');
                                }
                            });
                        }
                    }
                }
            $query->orWhere('id', $keyword);
        }
        return $query;
    }

    /**
     * @param array $relationships
     * @param int $per_page
     * @return array|null
     */
    public function all_with_pagination(array $relationships = [], int $per_page = 10): ?array
    {
        $query = $this->model->with($relationships);
        $query = $this->add_search($query);
        $query = $this->orderQueryBy($query);
        $all = $query->paginate($per_page);

        if (count($all) > 0) {
            $pagination_data = $this->formatPaginateData($all);

            return ['data' => $all, 'pagination_data' => $pagination_data];
        }

        return null;
    }

    /**
     * @param $data
     * @return array
     */
    #[ArrayShape(['currentPage' => "mixed", 'from' => "mixed", 'to' => "mixed", 'total' => "mixed", 'per_page' => "mixed"])]
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

    /**
     * @param $path
     * @return mixed
     */
    private function makeDirectory($path): mixed
    {
        $this->files->makeDirectory($path, 0777, true, true);

        return $path;
    }

    /**
     * @param array $data
     * @param array $relationships
     * @return mixed
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function create(array $data, array $relationships = []): mixed
    {
        $received_data = $data;
        $col_name = $this->fileColName($data);
        $file = $this->storeUpdateFileIfExist($col_name, $data);
        $result = $this->model->create($data);
        $result->refresh();
        $result = $this->addFileToModel($file, $result, $received_data, $col_name);
        return $result->load($relationships);
    }

    /**
     * @param $data
     * @return int|string
     */
    private function fileColName($data): int|string
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fileColumnsName)) {
                return $key;
            }
        }

        return '';
    }

    /**
     * @param string $col_name
     * @param $data
     * @param bool $is_store
     * @param $item
     * @return string
     */
    private function storeUpdateFileIfExist(string $col_name, &$data, bool $is_store = true, $item = null): string
    {
        $image = '';
        if ($col_name != '') {
            if (array_key_exists($col_name, $data)) {
                $this->files = new Filesystem();
                if ($is_store) {
                    $this->makeDirectory(storage_path('app/public/' . $this->model->getTable()));
                    $image = $this->storeFile($data["$col_name"], $this->model->getTable());
                } else {
                    if ($item->{"$col_name"}) {
                        $image = $this->updateFile($data["$col_name"], $item->{"$col_name"}, $this->model->getTable());
                    } else {
                        $image = $this->storeFile($data["$col_name"], $this->model->getTable());
                    }
                }
                unset($data["$col_name"]);
            }

            return $image;
        }

        return '';
    }

    /**
     * @param $file
     * @param $model
     * @param $data
     * @param $col_name
     * @return mixed
     */
    private function addFileToModel($file, $model, $data, $col_name): mixed
    {
        if ($col_name != '') {
            if (array_key_exists($col_name, $data)) {
                $model->{"$col_name"} = $file;
                $model->save();
            }
        }

        return $model;
    }

    /**
     * @param array $data
     * @param $id
     * @param array $relationships
     * @return mixed|null
     * @noinspection PhpUndefinedMethodInspection
     */
    public function update(array $data, $id, array $relationships = []): mixed
    {
        $received_data = $data;
        $item = $this->model->where('id', '=', $id)->first();
        if ($item) {
            $col_name = $this->fileColName($data);
            $file = $this->storeUpdateFileIfExist($col_name, $data, false, $item);
            $item->fill($data);
            $item->save();
            $this->addFileToModel($file, $item, $received_data, $col_name);

            return $item->load($relationships);
        }

        return null;
    }

    /**
     * @param $id
     * @return bool|null
     * @noinspection PhpUndefinedMethodInspection
     */
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
     * @param $id
     * @param array $relationships
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
}
