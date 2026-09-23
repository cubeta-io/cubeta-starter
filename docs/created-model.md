# Created Models

The created models will have methods that help you control the flow of your application

## searchableArray method

the defined `searchableArray` method will power your index controllers with the ability to search inside the returned
columns on within the requested query but this is a limitation to the usage of the `globalQuery` method in your model
corresponding repository . check on the `allWithPagination` method in the base repository then its call from the base
service class .

this mean querying your models like this :

```php
class PostRepository extends BaseRepository
{
    public function index(){
        return $this->globalQuery()->where('likes' , '>' , 50)->paginate(10);
    }
}
```

will give you the ability to use a query param in your request named **_search_** with the value of the keyword you want
to search and the query will search for it in the defined columns ,so if we have this columns :

```php
public function searchableArray() : array
{
    return [
        'title' , 
        'body'
    ];   
}
```

it will search just within the **title** and **body** columns .

## relationsSearchableArray method

the same things in the `searchableArray` method applied here just instead in searching in the table columns it searches
in the related tables columns and its definition looks like this :

```php
class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function author()
    {
        return $this->belongsTo(Author::class);    
    }
    
    public function relationsSearchableArray() 
    {
        return [
            'comments' => [
                'body'
            ] , 
            'author' => [
                'name'
            ]
        ];
    }
}
```

this mean you could search using the search query param inside the body column of posts comments and in the posts author
names.

## filterArray method

we will not generate this method in your models , so you have to add it manually as it is very customizable but in basic
it powers up your `globalQuery` with the ability to filter the returned values .

So basically this method will return a nested array like the following :

```php
public function filterArray() : array
{
    return [
        [
            'field' => 'views', // this key could also be name so : 'name' => 'likes'
            'operator' => '>=', // the default value is '=' , it could also set to 'like' operator or whatever where clause operator 
            'method' => 'where', // it is a query so set the method you like it to filter  : 'whereColumn'  , default value is 'where' 
            'query' => null, // here you can provide a call back function that takes an instance of the global query implemented within your query to do whatever you want
        ],
        [
            'name' => 'likes', // the relation column to filter
            'relation' => 'comments' , // the relation we want to filter the query depending on it
        ],
    ]
}
```

so basically after having the previous `filterArray` method we gain the ability to send query params named **_views_**
or **_likes_** with values represent the desired data for example and based on the defined method :

- sending a query param within the request named `views` with the value of `10` while querying using the `globalQuery`
  will return all the posts which have views more than `10`.
- sending a query param within the request named `likes` with the value of `10` while querying using the `globalQuery`
  will return all the posts which have comments with likes **equals** to `10`.

providing the query params values as arrays that represent a range will ignore the defined method and use
the `whereBetween` method to query your data where the first array element and the second one fits in the `whereBetween`
method.

## ordering results

When querying a model using its repository `globalQuery` method you can order the results based on each column in the
corresponding table by using 2 query params :

1. `sort_col` as the name of the column you want to order the results based on it.
2. `sort_dir` as the order direction `asc` , `desc`.

so when your request contain those params and your request uses the `globalQuery` method it will return the response
data ordered by the selected column .

> [!WARNING]
> the functionality of the previous methods depends on using the `globalQuery` method based in the BaseRepository class
> read more about it [here](base-repository.md#baserepository-class)

## File columns (MediaCast & HasMedia)

File columns are handled through the `MediaCast` cast and the `HasMedia` trait, both added to the model automatically
when it has a `file` column — you don't declare a list of file keys by hand.

A column cast with `MediaCast::single(private: false)` (one file) or `MediaCast::array(private: true)` (multiple files)
is treated as a file column. When you create or update a record through the repository's `create` / `update` methods
and pass an `UploadedFile` for that column, the file is stored on the disk configured in `config/media.php` (see
[Configuration](configuration.md#media-settings)) under a `public` or `private` path prefix depending on the `private`
flag, and a `SerializedMedia` value object — serialized as JSON containing the storage path, size, extension and MIME
type — is saved in the column.

Reading the attribute back gives you a `SerializedMedia` instance (or an array of them for `::array()` columns) with:

- `url()` — a public URL for public files, or a short-lived signed URL (TTL from `media.temporary_url_ttl`) for
  private files on disks that support temporary URLs
- `exists()`, `contents()`, `delete()`, `getUploadedFile()`, `copyTo()`

`HasMedia` keeps the disk in sync with the column automatically:

- on **update**, if a column's value changes, the file (s) it previously pointed to are deleted once the transaction
  commits
- on **delete**, stored files are removed when the record is actually removed — for models using `SoftDeletes`, this
  only happens on a **force delete**, not a soft delete

```php
protected function casts(): array
{
    return [
        'image' => MediaCast::single(private: false),
        'attachments' => MediaCast::array(private: true),
    ];
}
```


