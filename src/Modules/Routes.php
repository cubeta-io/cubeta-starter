<?php

namespace Cubeta\CubetaStarter\Modules;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\Settings;
use Exception;
use Illuminate\Support\Collection;
use Stringable;

class Routes implements Stringable
{
    readonly public string $version;
    readonly public ?string $actor;
    readonly public string $pathname;
    readonly public ?string $controllerName;
    readonly public ?string $actionName;
    readonly public string $method;
    readonly public string $name;
    readonly public ?string $viewName;
    readonly public string $url;

    public static function actorUrlNaming(string|null $actor): Stringable|null
    {
        return $actor
            ? str($actor)->singular()->lower()->snake()->replace('_', '-')
            : null;
    }

    public static function actorRouteNameNaming(string|null $actor): Stringable|null
    {
        return $actor
            ? str($actor)->singular()->replace('_', '.')
            : null;
    }

    public static function actorRouteFileNaming(string|null $actor): Stringable|null
    {
        return $actor
            ? str($actor)->singular()->lower()->snake()->replace('_', '-')
            : null;
    }

    /**
     * @param string|null $actor
     * @param string      $pathname
     * @param string|null $controllerName
     * @param string|null $actionName
     * @param string      $method
     * @param string      $name
     * @param string|null $viewName
     * @throws Exception
     */
    public function __construct(?string $actor, string $pathname, ?string $controllerName, ?string $actionName, string $method, string $name, ?string $viewName)
    {
        $this->version = config('cubeta-starter.version');
        $this->actor = $actor;
        $this->pathname = str($pathname)
            ->when(
                fn($s) => $s->startsWith("/"),
                fn($s) => $s->replaceFirst("/", '')
            )->toString();

        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->method = $method;
        $this->name = $name;
        $this->viewName = $viewName;


        $this->url = str("")
            ->when(
                !empty($this->version),
                fn($r) => $r->append("/$this->version"),
                fn($r) => $r->append("/")
            )->when(
                !empty($this->actor) && $this->actor != "none",
                fn($r) => $r->append("/")->append(self::actorUrlNaming($this->actor)),
                fn($r) => $r->endsWith("/") ? $r : $r->append("/")
            )->when(
                fn($r) => $r->endsWith("/"),
                fn($r) => $r->append($this->pathname),
                fn($r) => $r->append("/$this->pathname")
            );

        if ($this->invalidViewRoute()) {
            throw new Exception("You have to define the view name when using the [$this->method] method");
        }

        if ($this->invalidControllerRoute()) {
            throw new Exception("You have to define the controller class and its action when using [$this->method] route method");
        }

        if ($this->invalidResourceRoute()) {
            throw new Exception("You have to define the controller class when using the [$this->method] method");
        }
    }

    private function invalidViewRoute(): bool
    {
        return ($this->method == "view" || $this->method == "inertia")
            && empty($this->viewName);
    }

    private function invalidControllerRoute(): bool
    {
        return (
                $this->method != "view"
                && $this->method != "inertia"
                && $this->method != "Resource"
                && $this->method != "apiResource"
            ) && empty($this->controllerName)
            && empty($this->actionName);
    }

    private function invalidResourceRoute(): bool
    {
        return ($this->method == "Resource" || $this->method == "apiResource")
            && empty($this->controllerName);
    }

    public function isViewRoute(): bool
    {
        return $this->method == "view"
            || $this->method == "inertia";
    }

    public function isResourceRoute(): bool
    {
        return $this->method == "resource"
            || $this->method == "apiResource";
    }

    public function __toString(): string
    {
        return str("Route::$this->method('$this->url', ")
            ->when(
                $this->isViewRoute(),
                fn($r) => $r->append("'$this->viewName'"),
            )->when(
                $this->isResourceRoute(),
                fn($s) => $s->append("$this->version\\$this->controllerName::class")
            )->when(
                !$this->isViewRoute() && !$this->isResourceRoute(),
                fn($s) => $s->append("[$this->version\\$this->controllerName::class , '$this->actionName']")
            )
            ->append(")")
            ->when(
                $this->isResourceRoute(),
                fn($s) => $s->append("->names('$this->name');"),
                fn($s) => $s->append("->name('$this->name');")
            );
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    private static function initializeAuthRouteData(string $container, string|null $actor, string $actionInRouteName, bool $isPublic = true): array
    {
        $version = config('cubeta-starter.version');
        $actor = ContainerType::isWeb($container) ? "dashboard" : $actor;
        $controllerName = ContainerType::isWeb($container)
            ? "BaseAuthController"
            : Naming::model($actor) . "AuthController";
        $name = str($version)
            ->when(
                ContainerType::isWeb($container),
                fn($s) => $s->append(".web"),
                fn($s) => $s->append(".api")
            )
            ->when(
                $isPublic,
                fn($s) => $s->append(".public")
            )->when(
                !$isPublic && ContainerType::isWeb($container),
                fn($s) => $s->append(".protected")
            )
            ->when(
                ContainerType::isApi($container) && !is_null($actor),
                fn($s) => $s->append(".")->append(self::actorRouteNameNaming($actor))
            )->append(".$actionInRouteName");

        return [
            $actor,
            $controllerName,
            $name
        ];
    }

    public static function login(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "login");
        return new self(
            actor: $actor,
            pathname: "login",
            controllerName: $controllerName,
            actionName: "login",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function register(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "register");
        return new self(
            actor: $actor,
            pathname: "register",
            controllerName: $controllerName,
            actionName: "register",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function requestResetPassword(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "request.reset.password");
        return new self(
            actor: $actor,
            pathname: "request-reset-password",
            controllerName: $controllerName,
            actionName: "requestResetPassword",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function validateResetCode(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "validate.reset.password.code");
        return new self(
            actor: $actor,
            pathname: "validate-reset-password-code",
            controllerName: $controllerName,
            actionName: "validateResetPasswordCode",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function resetPassword(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "reset.password");
        return new self(
            actor: $actor,
            pathname: "reset-password",
            controllerName: $controllerName,
            actionName: "resetPassword",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function refreshToken(string|null $actor): Routes
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            actor: $actor,
            pathname: "refresh",
            controllerName: $controllerName,
            actionName: "refresh",
            method: "post",
            name: "$version.api.$actor.refresh.token",
            viewName: null
        );
    }

    public static function logout(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "logout", false);
        return new self(
            actor: $actor,
            pathname: "logout",
            controllerName: $controllerName,
            actionName: "logout",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function updateUser(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "me.update", false);
        return new self(
            actor: $actor,
            pathname: "me",
            controllerName: $controllerName,
            actionName: "updateUserDetails",
            method: "put",
            name: $name,
            viewName: null
        );
    }

    public static function me(string $container, string|null $actor): Routes
    {
        list($actor, $controllerName, $name) = self::initializeAuthRouteData($container, $actor, "me", false);
        return new self(
            actor: $actor,
            pathname: "me",
            controllerName: $controllerName,
            actionName: "userDetails",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function loginPage(): Routes
    {
        $version = config('cubeta-starter.version');
        $frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        return new self(
            actor: "dashboard",
            pathname: "login",
            controllerName: null,
            actionName: null,
            method: $frontType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: "$version.web.public.login.page",
            viewName: Views::login()->name
        );
    }

    public static function registerPage(): Routes
    {
        $version = config('cubeta-starter.version');
        $frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        return new self(
            actor: "dashboard",
            pathname: "register",
            controllerName: null,
            actionName: null,
            method: $frontType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: "$version.web.public.register.page",
            viewName: Views::register()->name
        );
    }

    public static function forgetPasswordPage(): Routes
    {
        $version = config('cubeta-starter.version');
        $frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        return new self(
            actor: "dashboard",
            pathname: "request-reset-password",
            controllerName: null,
            actionName: null,
            method: $frontType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: "$version.web.public.request.reset.password.page",
            viewName: Views::forgetPassword()->name
        );
    }

    public static function resetPasswordPage(): Routes
    {
        $version = config('cubeta-starter.version');
        $frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        return new self(
            actor: "dashboard",
            pathname: "reset-password",
            controllerName: null,
            actionName: null,
            method: $frontType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: "$version.web.public.reset.password.page",
            viewName: Views::resetPassword()->name
        );
    }

    /**
     * @param string|null $actor
     * @return Collection<Routes>
     */
    public static function apiPublicAuthRoutes(string|null $actor): Collection
    {
        return collect([
            self::login(ContainerType::API, $actor),
            self::register(ContainerType::API, $actor),
            self::requestResetPassword(ContainerType::API, $actor),
            self::resetPassword(ContainerType::API, $actor),
            self::validateResetCode(ContainerType::API, $actor),
        ]);
    }

    /**
     * @param string|null $actor
     * @return Collection<Routes>
     */
    public static function apiProtectedAuthRoutes(string|null $actor): Collection
    {
        return collect([
            self::me(ContainerType::API, $actor),
            self::updateUser(ContainerType::API, $actor),
            self::refreshToken($actor),
        ]);
    }

    /**
     * @return Collection<Routes>
     */
    public static function webPublicAuthRoutes(): Collection
    {
        return collect([
            self::login(ContainerType::WEB, null),
            self::register(ContainerType::WEB, null),
            self::requestResetPassword(ContainerType::WEB, null),
            self::resetPassword(ContainerType::WEB, null),
            self::validateResetCode(ContainerType::WEB, null),

            self::registerPage(),
            self::loginPage(),
            self::forgetPasswordPage(),
            self::resetPasswordPage(),
        ]);
    }

    /**
     * @return Collection<Routes>
     */
    public static function webProtectedAuthRoutes(): Collection
    {
        return collect([
                self::me(ContainerType::WEB, null),
                self::updateUser(ContainerType::WEB, null),
                self::logout(ContainerType::WEB, null),
            ]
        );
    }

    public static function resource(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model);
        return new self(
            actor: $actor,
            pathname: $model->routeUrlNaming(),
            controllerName: $model->getControllerName(),
            actionName: null,
            method: $container == ContainerType::API ? "apiResource" : "resource",
            name: $name,
            viewName: null
        );
    }

    private static function getResourceRouteName(?string $actor, string $container, CubeTable $model): string
    {
        $version = config('cubeta-starter.version');
        if (!isset($actor) || $actor == '' || $actor == 'none') {
            if (
                (ContainerType::isApi($container) && Settings::make()->installedApiAuth())
                || (ContainerType::isWeb($container) && Settings::make()->installedWebAuth())
            ) {
                $name = "$version.{$container}.protected.{$model->routeNameNaming()}";
            } else {
                $name = "$version.{$container}.public.{$model->routeNameNaming()}";
            }
        } else {
            $name = "$version.{$container}.{" . self::actorRouteNameNaming($actor) . "}.{$model->routeNameNaming()}";
        }
        return $name;
    }

    public static function index(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".index";
        return new self(
            $actor,
            pathname: $model->routeUrlNaming(),
            controllerName: $model->getControllerName(),
            actionName: "index",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function show(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".show";
        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/{{$model->idVariable()}}",
            controllerName: $model->getControllerName(),
            actionName: "show",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function create(CubeTable $model, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, ContainerType::WEB, $model) . ".create";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/create",
            controllerName: $model->getControllerName(),
            actionName: "create",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function store(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".store";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming(),
            controllerName: $model->getControllerName(),
            actionName: "store",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function edit(CubeTable $model, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, ContainerType::WEB, $model) . ".edit";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/{{$model->idVariable()}}/edit",
            controllerName: $model->getControllerName(),
            actionName: "edit",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function update(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".update";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/{{$model->idVariable()}}",
            controllerName: $model->getControllerName(),
            actionName: "update",
            method: "put",
            name: $name,
            viewName: null
        );
    }

    public static function delete(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".destroy";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/{{$model->idVariable()}}",
            controllerName: $model->getControllerName(),
            actionName: "destroy",
            method: "delete",
            name: $name,
            viewName: null
        );
    }

    public static function data(CubeTable $model, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, ContainerType::WEB, $model) . ".data";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/data",
            controllerName: $model->getControllerName(),
            actionName: "data",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function import(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".import";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/import",
            controllerName: $model->getControllerName(),
            actionName: "import",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function export(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".export";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/export",
            controllerName: $model->getControllerName(),
            actionName: "export",
            method: "post",
            name: $name,
            viewName: null
        );
    }

    public static function importExample(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, $container, $model) . ".import.example";

        return new self(
            $actor,
            pathname: $model->routeUrlNaming() . "/get-import-example",
            controllerName: $model->getControllerName(),
            actionName: "getImportExample",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    public static function dashboardPage(?string $actor = null): Routes
    {
        $actor = self::actorRouteNameNaming($actor);
        $version = config('cubeta-starter.version');
        $name = "$version.web.$actor.index";
        $frontendType = Settings::make()->getFrontendType();

        return new self(
            actor: "dashboard",
            pathname: "/",
            controllerName: null,
            actionName: null,
            method: $frontendType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: $name,
            viewName: Views::dashboard()->name,
        );
    }

    public static function allPaginatedJson(CubeTable $model, ?string $actor = null): Routes
    {
        $name = self::getResourceRouteName($actor, ContainerType::WEB, $model) . ".all.paginated.json";
        return new self(
            actor: "dashboard",
            pathname: $model->routeUrlNaming() . "/all-paginated-json",
            controllerName: $model->getControllerName(),
            actionName: "allPaginatedJson",
            method: "get",
            name: $name,
            viewName: null
        );
    }

    /**
     * @param CubeTable   $model
     * @param string      $container
     * @param string|null $actor
     * @return Collection<Routes>|Routes[]
     */
    public static function crudRoutes(CubeTable $model, string $container = ContainerType::API, ?string $actor = null): Collection|array
    {
        if (ContainerType::isApi($container)) {
            return collect([
                self::export($model, ContainerType::API, $actor),
                self::import($model, ContainerType::API, $actor),
                self::importExample($model, ContainerType::API, $actor),
                self::resource($model, ContainerType::API, $actor),
            ]);
        }

        return collect([
            self::export($model, ContainerType::WEB, $actor),
            self::import($model, ContainerType::WEB, $actor),
            self::importExample($model, ContainerType::WEB, $actor),
            self::data($model, $actor),
            self::resource($model, ContainerType::WEB, $actor),
        ]);
    }
}