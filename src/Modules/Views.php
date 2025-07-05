<?php

namespace Cubeta\CubetaStarter\Modules;

use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\Settings;

class Views
{
    public readonly string $name;
    public readonly string $fileName;
    public readonly CubePath $path;

    /**
     * @param string   $name
     * @param string   $fileName
     * @param CubePath $path
     */
    public function __construct(string $name, string $fileName, CubePath $path)
    {
        $this->name = $name;
        $this->fileName = $fileName;
        $this->path = $path;
    }


    public static function index(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.index');

        list($name, $path) = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.index'),
            $path
        );
    }

    public static function create(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.create');

        list($name, $path) = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.create'),
            $path
        );
    }

    public static function edit(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.edit');

        list($name, $path) = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.edit'),
            $path
        );
    }

    public static function show(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.show');

        list($name, $path) = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.show'),
            $path
        );
    }

    private static function getPathAndViewName(?string $actor, CubeTable $model, mixed $viewName): array
    {
        $frontendType = Settings::make()->getFrontendType();
        $extension = $frontendType == FrontendTypeEnum::REACT_TS
            ? "tsx"
            : "blade.php";

        $resourcePath = $frontendType == FrontendTypeEnum::REACT_TS
            ? "resources/js/Pages"
            : "resources/views";

        $nameSeparator = $frontendType == FrontendTypeEnum::REACT_TS
            ? "/"
            : ".";

        $name = !empty($actor) && $actor != 'none'
            ? "dashboard{$nameSeparator}{$actor}{$nameSeparator}{$model->viewNaming()}{$nameSeparator}{$viewName}"
            : "dashboard{$nameSeparator}{$model->viewNaming()}{$nameSeparator}{$viewName}";

        $path = CubePath::make(
            !empty($actor) && $actor != 'none'
                ? "$resourcePath/dashboard/$actor/{$model->viewNaming()}/$viewName.{$extension}"
                : "$resourcePath/dashboard/{$model->viewNaming()}/$viewName.{$extension}"
        );
        return array($name, $path);
    }

    public static function login(): Views
    {
        return self::getAuthView(config('views-names.login'));
    }

    public static function register(): Views
    {
        return self::getAuthView(config('views-names.register'));
    }

    public static function forgetPassword(): Views
    {
        return self::getAuthView(config('views-names.forget-password'));
    }

    public static function resetPassword(): Views
    {
        return self::getAuthView(config('views-names.reset-password'));
    }

    public static function userDetails(): Views
    {
        return self::getAuthView(config('views-names.user-details'));
    }

    public static function resetPasswordCodeForm(): Views
    {
        return self::getAuthView(config('views-names.reset-password-code-form'));
    }

    private static function getAuthView(string $name): Views
    {
        $frontendType = Settings::make()->getFrontendType();
        $extension = $frontendType == FrontendTypeEnum::REACT_TS
            ? "tsx"
            : "blade.php";
        $resourcePath = $frontendType == FrontendTypeEnum::REACT_TS
            ? "resources/js/Pages"
            : "resources/views";

        $path = "{$resourcePath}/" . $name . ".$extension";

        return new self(
            name: $name,
            fileName: $name,
            path: CubePath::make($path),
        );
    }

    public static function dashboard(): Views
    {
        $frontendType = Settings::make()->getFrontendType();
        $extension = $frontendType == FrontendTypeEnum::REACT_TS
            ? "tsx"
            : "blade.php";
        $resourcePath = $frontendType == FrontendTypeEnum::REACT_TS
            ? "resources/js/Pages"
            : "resources/views";
        return new self(
            name: "index",
            fileName: "index.$extension",
            path: CubePath::make("$resourcePath/index.$extension"),
        );
    }
}