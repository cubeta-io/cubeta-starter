# Translatable Attributes Handling

To handle translatable attributes (attributes with multi locale values) you have the following classes :

1. `\App\Casts\Translatable::class`
2. `\App\Serializers\Translatable::class`

the cast class is a laravel cast class its purpose is to cast the attributes using the `\App\Serializers\Translatable`
serializer class so your base focus should be in the serializer class .

## Basic usage

let us assume the following :

you have a table **_posts_** with the following migration file :

```php
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->json('title');
        $table->json('body');
        $table->string('author');

        $table->timestamps();
    });
}
```

the **title** and **body** columns are localized data (has data in multiple languages) .

Then a corresponding model for this migration would be :

```php
<?php

namespace App\Models;

class Post extends Model
{
    protected $fillable = [
        'title',
        'body',
        'author',
    ];
    protected $casts = [
        'title' => \App\Casts\Translatable::class,
        'body' => \App\Casts\Translatable::class,
    ];
}
```

now the title and body properties will be cast to `App\Serializers\Translatable` object

the translatable serializer depends on the locales defined in your `config/cubeta-starter.php` file so make sure to add
your available locales in it.

## Getting data

let us assume we have the following locales **en** , **ar** defined in the package config file , then accessing those
locales will be valid via the following methods :

```php
$post = \App\Models\Post::first(); // title : {"en" : "english title" , "ar" : "arabic title"}

$post->title->en // english title
$post->title->ar // arabic title

$post->title->translate("en") // english title

$post->title // will return the value based on your application current locale
$post->title->translate() // will return the value based on your application current locale

$post->toArray(); // will return the value as json decoded array ["en" => "english title" , "ar" => "arabic title"]
$post->toJson(); // will return the json value of the data "{"en" : "english title" , "ar" : "arabic title"}"
```

## A special case for the usage in laravel **Api Resources** :

of course, you have full access to the previous mentioned methods to access data of your object but to make it easier to
control your api responses the translatable serializer will implement `JsonSerializable` interface, so you have to
implement the `jsonSerialize` method and if you take a look at the serializer class you'll see this implementation

```php
    public function jsonSerialize(): mixed
    {
        return $this->toJson();
    }
```

the reason of this is to define the shape that the translatable object is handled in the api responses so the previous
implementation represent the translatable object in the api responses as a json string so feel free to customize it as
you want

for example :

```php
class PostResource extends BaseResource
{
    public function toArray($request): array
        {
            return [
                'id' => $this->id,
                'title' => $this->title,
                'body' => $this->body,
                'author' => $this->author,
            ];
    }
}
```

based on the default implementation of the `jsonSerialize` method the **title** and **body** will be returned as json
strings in the response

## storing data

the translatable cast accept data as arrays and as json strings so the following is acceptable :

```php
$post = \App\Models\Post::create([
    "title" => '{"en" : "english title" , "ar" : "arabic title"}' , 
    // the rest of the columns
]);

$post = \App\Models\Post::create([
    "title" => ["en" => "english title" , "ar" => "arabic title"], 
    // the rest of the columns
]);
```

in addition to that you can set values directly to the translatable object like this :

```php
$post->title->en = "new english title" ; 
$post->title->ar = "new arabic title" ; 
$post->save();
```

and you could also do the following :

```php
$post->title = new \App\Serializers\Translatable(["en" => "english title" , "ar" => "arabic title"]);
$post->title = new \App\Serializers\Translatable('{"en" : "english title" , "ar" : "arabic title"}');
$post->save();
```

## Searching

when searching it is convenient to use the `LIKE` operator in where clauses but for a deterministic results you have to
use `JSON_CONTAINS` as an operator in where clauses.

## Last thing

the translatable serializer class provide a faker method, so you could use it like this :

```php
\App\Serializers\Translatable::fake('word') ; // fake()->word() but one for each defined locale
\App\Serializers\Translatable::fake('firstName') ;
.... 
```
