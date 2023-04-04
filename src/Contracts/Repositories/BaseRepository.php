<?php

namespace cubeta\CubetaStarter\Contracts\Repositories;

use Cubeta\CubetaStarter\Traits\FileHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;

/**
 * Class BaseRepository
 */
abstract class BaseRepository implements IBaseRepository
{
    use FileHandler;

    protected $model;

    private $files;

    private $file_columns_name = ['image', 'icon', 'image_profile', 'slot_icon', 'logo', 'image_url'];

    /**
     * BaseRepository constructor.
     *
     * @param  Model  $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param  array  $relationships
     * @return mixed
     */
    public function all(array $relationships = [])
    {
        return $this->model->with($relationships)->orderBy('created_at', 'desc')->get();
    }

    /**
     * paginated data
     *
     * @param  array  $relationships
     * @param  int  $per_page
     * @return mixed
     */
    public function all_with_pagination(array $relationships = [], $per_page = 10)
    {
        $all = $this->model->with($relationships)->orderBy('created_at', 'desc')->paginate($per_page);
        $pagination_data = null;
        if (count($all) > 0) {
            $pagination_data = $this->formatPaginateData($all);

            return ['data' => $all, 'pagination_data' => $pagination_data];
        }

        return null;
    }

    public function formatPaginateData($data)
    {
        $paginated_arr = $data->toArray();

        return $paginateData = [
            'currentPage' => $paginated_arr['current_page'],
            'from' => $paginated_arr['from'],
            'to' => $paginated_arr['to'],
            'total' => $paginated_arr['total'],
            'per_page' => $paginated_arr['per_page'],
        ];
    }

    /**
     * make directory for files
     *
     * @param $path
     * @return mixed
     */
    private function makeDirectory($path)
    {
        $this->files->makeDirectory($path, 0777, true, true);

        return $path;
    }

    /**
     * @param  array  $data
     * @param  array  $relations
     * @return mixed
     */
    public function create(array $data, array $relations = [])
    {
        $received_data = $data;
        $col_name = $this->fileColName($data);
        $file = $this->storeUpdateFileIfExist($col_name, $data);
        $result = $this->model->create($data);
        $result->refresh();
        $result = $this->addFileToModel($file, $result, $received_data, $col_name);
        if ($result) {
            return $result->load($relations);
        }

        return null;
    }

    /**
     * this function search in file columns name and return the name of the col
     *
     * @param $data
     * @return int|string
     */
    private function fileColName($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->file_columns_name)) {
                return $key;
            }
        }

        return '';
    }

    /**
     * store file in directory same as table name in the storage and return the image
     *
     * @param $col_name
     * @param $data
     * @param  bool  $is_store
     * @param  null  $item
     * @return string
     */
    private function storeUpdateFileIfExist($col_name, &$data, $is_store = true, $item = null)
    {
        $image = '';
        if ($col_name != '') {
            if (array_key_exists($col_name, $data)) {
                $this->files = new Filesystem();
                if ($is_store) {
                    $this->makeDirectory(storage_path('app/public/'.$this->model->getTable()));
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
     * this method connect the stored file with the model after saving it
     *
     * @param $file
     * @param $model
     * @param $data
     * @param $col_name
     * @return mixed
     */
    private function addFileToModel($file, $model, &$data, $col_name)
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
     * @param  array  $data
     * @param  int  $id
     * @param  array  $relations
     * @return mixed
     */
    public function update(array $data, $id, array $relations = [])
    {
        $received_data = $data;
        $item = $this->model->where('id', '=', $id)->first();
        if ($item) {
            $col_name = $this->fileColName($data);
            $file = $this->storeUpdateFileIfExist($col_name, $data, false, $item);
            $item->fill($data);
            $item->save();
            $result = $this->addFileToModel($file, $item, $received_data, $col_name);

            if (isset($result)) {
                return $item->load($relations);
            }
        }

        return null;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
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
     * @param  array  $relationships
     * @return mixed
     */
    public function find($id, array $relationships = [])
    {
        $result = $this->model->with($relationships)->find($id);

        if ($result) {
            return $result;
        }

        return null;
    }
}
