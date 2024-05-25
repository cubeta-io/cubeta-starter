<?php

namespace Cubeta\CubetaStarter\App\Models\Postman;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Throwable;

class Postman
{
    public static ?CubePath $path;
    public static ?string $name;
    public static string $version = "v1";
    public static ?string $content = null;
    private static $instance;

    public function getCollection(): PostmanCollection
    {
        $data = json_decode(self::getContent(), true);
        return PostmanCollection::serialize($data ?? []);
    }

    public static function getContent(): bool|string
    {
        self::$name = config('cubeta-starter.project_name') . '.postman_collection.json';
        self::$path = CubePath::make(config('cubeta-starter.postman_collection_path') . self::$name);
        self::$path->ensureDirectoryExists();

        if (!self::$path->exist()) {
            self::initialize();
        }

        return self::$path->getContent();
    }

    public static function make(): Postman
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function initialize(): void
    {
        try {
            FileUtils::generateFileFromStub(
                [
                    "{projectName}" => config('cubeta-starter.project_name'),
                    "{project-url}" => self::getProjectUrl()
                ],
                self::$path->fullPath,
                __DIR__ . "/../../../stubs/postman-collection.stub"
            );
        } catch (BindingResolutionException|FileNotFoundException|Throwable|Exception $e) {
            CubeLog::add($e);
        }
    }

    private static function getProjectUrl()
    {
        $url = config('cubeta-starter.project_url') ?? "http://localhost/" . config('cubeta-starter.project_name') . "/public/api/";
        return str($url)->endsWith("/") ? $url : $url . "/";
    }
}
