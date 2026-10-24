<?php

namespace Cubeta\CubetaStarter\Modules;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;

class Views
{
    public readonly string $fileName;

    public readonly string $name;

    public readonly CubePath $path;

    public function __construct(string $name, string $fileName, CubePath $path)
    {
        $this->name = $name;
        $this->fileName = $fileName;
        $this->path = $path;
    }

    private static function getAuthView(string $name): Views
    {
        $frontendType = Settings::make()->getFrontendType();
        $extension = $frontendType == FrontendTypeEnum::REACT_TS
            ? 'tsx'
            : 'blade.php';
        $resourcePath = $frontendType == FrontendTypeEnum::REACT_TS
            ? 'resources/js/pages'
            : 'resources/views';

        $path = "{$resourcePath}/".$name.".$extension";

        return new self(
            name: $name,
            fileName: $name,
            path: CubePath::make($path),
        );
    }

    private static function getPathAndViewName(?string $actor, CubeTable $model, mixed $viewName): array
    {
        $frontendType = Settings::make()->getFrontendType();
        $extension = $frontendType == FrontendTypeEnum::REACT_TS
            ? 'tsx'
            : 'blade.php';

        $resourcePath = $frontendType == FrontendTypeEnum::REACT_TS
            ? 'resources/js/pages'
            : 'resources/views';

        $nameSeparator = $frontendType == FrontendTypeEnum::REACT_TS
            ? '/'
            : '.';

        $name = ! empty($actor) && $actor != 'none'
            ? "dashboard{$nameSeparator}{$actor}{$nameSeparator}{$model->viewNaming()}{$nameSeparator}{$viewName}"
            : "dashboard{$nameSeparator}{$model->viewNaming()}{$nameSeparator}{$viewName}";

        $path = CubePath::make(
            ! empty($actor) && $actor != 'none'
                ? "$resourcePath/dashboard/$actor/{$model->viewNaming()}/$viewName.{$extension}"
                : "$resourcePath/dashboard/{$model->viewNaming()}/$viewName.{$extension}"
        );

        return [$name, $path];
    }

    public static function create(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.create');

        [$name, $path] = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.create'),
            $path
        );
    }

    public static function dashboard(): Views
    {
        $frontendType = Settings::make()->getFrontendType();
        $extension = $frontendType == FrontendTypeEnum::REACT_TS
            ? 'tsx'
            : 'blade.php';
        $resourcePath = $frontendType == FrontendTypeEnum::REACT_TS
            ? 'resources/js/pages'
            : 'resources/views';

        return new self(
            name: 'index',
            fileName: "index.$extension",
            path: CubePath::make("$resourcePath/index.$extension"),
        );
    }

    public static function edit(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.edit');

        [$name, $path] = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.edit'),
            $path
        );
    }

    public static function forgetPassword(): Views
    {
        return self::getAuthView(config('views-names.forget-password'));
    }

    public static function index(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.index');

        [$name, $path] = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.index'),
            $path
        );
    }

    public static function login(): Views
    {
        return self::getAuthView(config('views-names.login'));
    }

    public static function register(): Views
    {
        return self::getAuthView(config('views-names.register'));
    }

    public static function resetPassword(): Views
    {
        return self::getAuthView(config('views-names.reset-password'));
    }

    public static function resetPasswordCodeForm(): Views
    {
        return self::getAuthView(config('views-names.reset-password-code-form'));
    }

    public static function show(CubeTable $model, ?string $actor = null): self
    {
        $viewName = config('views-names.show');

        [$name, $path] = self::getPathAndViewName($actor, $model, $viewName);

        return new self(
            $name,
            config('views-names.show'),
            $path
        );
    }

    public static function userDetails(): Views
    {
        return self::getAuthView(config('views-names.user-details'));
    }
}
