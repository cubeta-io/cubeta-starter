<?php


namespace Cubeta\CubetaStarter\Implementations;

use Cubeta\CubetaStarter\Traits\FileHandler;
use Illuminate\Database\Eloquent\Model;
use Cubeta\CubetaStarter\Repository;
use Illuminate\Support\Collection;

class Eloquent implements Repository
{
    use FileHandler;
    /**
     * Find item by id
     * @param mixed $id
     * @param array $relations array of relations
     * @return Model|null
     */
    public function find($id , array $relations = [])
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * find Or Fail
     * @param $id
     * @param array $relations array of relations
     * @return mixed
     */
    public function findOrFail($id , array $relations = [])
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Return all items
     * @return Collection|null
     */
    public function all(array $relations = [])
    {
        return $this->model->with($relations)->get();
    }

    /**
     * Return query builder
     * @return Builder|null
     */
    public function query( array $relations = [])
    {
        return $this->model->with($relations)->query();
    }

    /**
     * Return all items paginated
     * @return mixed|null
     */
    public function allPaginated( array $relations = [] , $per_page = 10)
    {
        return $this->model->with($relations)->paginate($per_page);
    }

    /**
     * Create an item
     * @param array $data
     * @param array $filesKeys
     * @param bool $to_compress
     * @param bool $is_base_64
     * @param int $width
     * @return Model|null
     */
    public function create(array $data , array $filesKeys = [] , $to_compress = true,$is_base_64=false,$width = 300)
    {
        $data = $this->storeOrUpdateRequestedFiles($data,$filesKeys,true,null,$to_compress,$is_base_64,$width);
        return $this->model->create($data);
    }

    /**
     * Update a item
     * @param int|mixed $id
     * @param array $data
     * @param array $filesKeys
     * @param bool $to_compress
     * @param bool $is_base_64
     * @param int $width
     * @return bool|mixed
     */
    public function update($id, array $data , array $filesKeys = [], $to_compress = true, $is_base_64=false, $width = 300)
    {
        $item = $this->model->find($id);
        $data = $this->storeOrUpdateRequestedFiles($data,$filesKeys,false,$item,$to_compress,$is_base_64,$width);
        $item->fill($data)->save();
        return $item;
    }

    /**
     * destroy many item with primary key
     * @param array $ids
     * @return mixed
     */
    public function destroy(array $ids)
    {
        return $this->model->destroy($ids);
    }

    /**
     * delete item
     * @param Model|int $id
     * @return bool
     */
    public function delete($id)
    {
        $item = $this->model->find($id);
        if($item && $item->delete())
            return true;
        return false;
    }

    /**
     * update or create item
     * @param array $conditions
     * @param array $columns
     * @return mixed
     */
    public function updateOrCreate(array $conditions ,array $columns = [])
    {
        return $this->model->updateOrCreate($conditions,$columns);
    }
}
