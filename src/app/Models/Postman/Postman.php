<?php

namespace Cubeta\CubetaStarter\app\Models\Postman;

class Postman
{
    public static string $path;
    public static ?string $name;
    public static string $content = "{}";
    private static $instance;

    public static function make(): Postman
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        self::getContent();

        return self::$instance;
    }

    public static function getContent()
    {
        self::$name = config('cubeta-starter.project_name') . '.postman_collection.json';
        self::$path = base_path(config('cubeta-starter.postman_collection_path') . self::$name);

        if (!file_exists(self::$path)) {
            file_put_contents(self::$path, json_encode([]));
        } else {
            self::$content = file_get_contents(self::$path);
        }
    }

    public static function getCollection()
    {
        $data = json_decode(self::$content, true);
        return PostmanCollection::serialize($data ?? []);
    }
}
