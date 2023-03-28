<?php

namespace Cubeta\CubetaStarter;

use Cubeta\CubetaStarter\Traits\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service
{
    use JsonResponse;
    /**
     * Find an item by id
     * @param int $id
     * @param array $relations
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(int $id, array $relations = [])
    {
        $this->setCode(200)
                ->setMessage(__("cubeta.get_successfully"))
                ->setResult($this->mainInterface->find($id,$relations))
                ->setStatus(true);
        return $this->toJson();
    }

    /**
     * Return all items
     * @param array $relations
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(array $relations = [])
    {
        $this->setCode(200)
            ->setMessage(__("cubeta.get_successfully"))
            ->setResult($this->mainInterface->all($relations))
            ->setStatus(true);
        return $this->toJson();
    }

    /**
     * Return query builder
     * @param array $relations
     * @return Builder|null
     */
    public function query(array $relations = [])
    {
        return $this->mainInterface->query($relations);
    }

    /**
     * get all with pagination
     * @param array $relations
     * @param int $per_page
     * @return \Illuminate\Http\JsonResponse
     */
    public function allPaginated(array $relations = [] , $per_page = 10)
    {
        $data = $this->mainInterface->with($relations)->allPaginated($per_page);
        $this->setCode(200)
            ->setMessage(__("cubeta.get_successfully"))
            ->setResult($data)
            ->setPaginationData($data)
            ->setStatus(true);
        return $this->toJson();
    }

    /**
     * Create an item
     * @param array $data
     * @param array $filesKeys
     * @param bool $to_compress
     * @param bool $is_base_64
     * @param int $width
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(array $data, array $filesKeys = [] , $to_compress = true,$is_base_64=false,$width = 300)
    {
        $this->setCode(201)
            ->setMessage(__("cubeta.created_successfully"))
            ->setResult($this->mainInterface->create($data,$filesKeys,$to_compress,$is_base_64,$width))
            ->setStatus(true);
        return $this->toJson();
    }

    /**
     * Update a model
     * @param int|mixed $id
     * @param array|mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, array $data, array $filesKeys = [], $to_compress = true, $is_base_64=false, $width = 300)
    {
        $this->setCode(200)
            ->setMessage(__("cubeta.updated_successfully"))
            ->setResult($this->mainInterface->update($id, $data,$filesKeys,$to_compress,$is_base_64,$width))
            ->setStatus(true);
        return $this->toJson();
    }

    /**
     * Delete a model
     * @param int|Model $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $this->setCode(200)
            ->setMessage(__("cubeta.deleted_successfully"))
            ->setResult($this->mainInterface->delete($id))
            ->setStatus(true);
        return $this->toJson();
    }

    /**
     * multiple delete
     * @param array $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(array $id)
    {
        $this->setCode(200)
            ->setMessage(__("cubeta.deleted_successfully"))
            ->setResult($this->mainInterface->destroy($id))
            ->setStatus(true);
        return $this->toJson();
    }

    /**
     * update or create item
     * @param array $conditions
     * @param array $columns
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrCreate(array $conditions ,array $columns = [])
    {
        $this->setCode(200)
            ->setMessage(__("cubeta.updated_successfully"))
            ->setResult($this->mainInterface->updateOrCreate($conditions,$columns))
            ->setStatus(true);
        return $this->toJson();
    }
}
