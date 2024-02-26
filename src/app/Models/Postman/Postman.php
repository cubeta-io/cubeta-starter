<?php

namespace Cubeta\CubetaStarter\app\Models\Postman;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class Postman
{
    public static string $path;
    public static ?string $name;
    public static ?string $content = null;
    private static $instance;

    public static function make(): Postman
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function getCollection(): PostmanCollection
    {
        $data = json_decode(self::getContent(), true);
        return PostmanCollection::serialize($data ?? []);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public static function getContent(): bool|string
    {
        self::$name = config('cubeta-starter.project_name') . '.postman_collection.json';
        self::$path = base_path(config('cubeta-starter.postman_collection_path') . self::$name);
        ensureDirectoryExists(base_path(config('cubeta-starter.postman_collection_path')));

        if (!file_exists(self::$path)) {
            self::initialize();
        }

        return file_get_contents(self::$path);
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public static function initialize(): void
    {
        generateFileFromStub(
            [
                "{projectName}" => config('cubeta-starter.project_name'),
                "{project-url}" => self::getProjectUrl()
            ],
            self::$path,
            __DIR__ . "/../../../Commands/stubs/postman-collection.stub"
        );
    }

    private static function getProjectUrl()
    {
        return config('cubeta-starter.project_url') ?? "http://localhost/" . config('cubeta-starter.project_name') . "/public/";
    }
}
