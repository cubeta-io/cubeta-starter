# BaseRepository Class

- `globalQuery(array $relations = [])`
  this method will instantiate a unified query like the model global scope but with the abilities to add filter , search
  and order to the query this is done by the `addSearch` , `filterFields` , `orderQueryBy` and a specific arrays defined
  in your model check on that [here](created-model.md#created-models)
- `all(array $relations = [])` : <br>
  this method will return all the corresponding model records without any format

  if there is no data the function return `null`

  the relations parameter is to return the related models' data within the response, so you just have to pass the
  relation name for the desired relations data as an array like this `['products' , 'users']` <br>

- `all_with_pagination(array $relationships = [], $per_page = 10)` : <br>
  as above this method return all the data but paginated
  the return type is an array of the shape :

  `['data' => $all, 'pagination_data' => $pagination_data]`

  if there is no data the function return `null`


- `create(array $data, array $relations = [])` : <br>
  as its name this function accept an array of data and create an instance from the corresponding model, and then it
  returns the created model with the choice of relations to be in the response

  it will return null if something happened and the data hasn't created

- `update(array $data, $id, array $relations = [])` :  <br>
  its purpose is obvious soo no need to brief

  it will return null if something happened and the data hasn't updated

- `find($id, array $relationships = [])` <br>

  it will return null if the data hasn't found

- `delete($id)` <br>
  it will return `true` if the data has been deleted else it will return `null` .
