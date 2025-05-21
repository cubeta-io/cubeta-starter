<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Routes;

use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\Settings;
use Exception;

class RouteString
{
    readonly public string $version;
    readonly public ?string $actor;
    readonly public string $pathname;
    readonly public ?string $controllerName;
    readonly public ?string $actionName;
    readonly public string $method;
    readonly public string $name;
    readonly public ?string $viewName;


    /**
     * @param string|null $actor
     * @param string      $pathname
     * @param string|null $controllerName
     * @param string|null $actionName
     * @param string      $method
     * @param string      $name
     * @param string|null $viewName
     */
    public function __construct(?string $actor, string $pathname, ?string $controllerName, ?string $actionName, string $method, string $name, ?string $viewName)
    {
        $this->version = config('cubeta-starter.version');
        $this->actor = $actor;
        $this->pathname = $pathname;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->method = $method;
        $this->name = $name;
        $this->viewName = $viewName;
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


    /**
     * @throws Exception
     */
    public function __toString(): string
    {
        if ($this->invalidViewRoute()) {
            throw new Exception("You have to define the view name when using the [$this->method] method");
        }

        if ($this->invalidControllerRoute()) {
            throw new Exception("You have to define the controller class and its action when using [$this->method] route method");
        }

        if ($this->invalidResourceRoute()) {
            throw new Exception("You have to define the controller class when using the [$this->method] method");
        }

        return str("Route::$this->method('")
            ->when(
                !empty($this->version),
                fn($r) => $r->append("/$this->version"),
                fn($r) => $r->append("/")
            )->when(
                !empty($this->actor) && $this->actor != "none",
                fn($r) => $r->append("/{$this->actor}"),
                fn($r) => $r->endsWith("/") ? $r : $r->append("/")
            )->when(
                fn($r) => $r->endsWith("/"),
                fn($r) => $r->append($this->pathname),
                fn($r) => $r->append("/$this->pathname")
            )->append("' , ")
            ->when(
                $this->method == "view" || $this->method == "inertia",
                fn($r) => $r->append(", '$this->viewName'"),
            )->when(
                $this->method == "Resource" || $this->method == "apiResource",
                fn($r) => $r->append("[{$this->version}\\{$this->controllerName}::class , '$this->actionName']")
            )->append(")")
            ->append("->name('$this->name')");
    }

    public static function apiLogin(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "login",
            $controllerName,
            "login",
            "post",
            "$version.api.public" . $actor == null ? '.' : ".$actor." . "login",
            null
        );
    }

    public static function apiRegister(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "register",
            $controllerName,
            "register",
            "post",
            "$version.api.public" . $actor == null ? '.' : ".$actor." . "register",
            null
        );
    }

    public static function apiResetPasswordRequest(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "password-reset-request",
            $controllerName,
            "passwordResetRequest",
            "post",
            "$version.api.public" . $actor == null ? '.' : ".$actor." . "reset.password.request",
            null
        );
    }

    public static function apiValidateResetCode(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "check-reset-password-code",
            $controllerName,
            "checkPasswordResetCode",
            "post",
            "$version.api.public" . $actor == null ? '.' : ".$actor." . "check.reset.password.code",
            null
        );
    }

    public static function apiPasswordReset(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "reset-password",
            $controllerName,
            "passwordReset",
            "post",
            "$version.api.public" . $actor == null ? '.' : ".$actor." . "password.reset",
            null
        );
    }

    public static function apiRefreshToken(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "refresh",
            $controllerName,
            "refresh",
            "post",
            "$version.api.$actor.refresh.token",
            null
        );
    }

    public static function apiLogout(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "logout",
            $controllerName,
            "logout",
            "post",
            "$version.api.$actor.logout",
            null
        );
    }

    public static function apiUpdateUser(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            $actor,
            "update-user-data",
            $controllerName,
            "updateUserDetails",
            "post",
            "$version.api.$actor.update.user.data",
            null
        );
    }

    public static function apiUserDetails(string|null $actor): RouteString
    {
        $version = config('cubeta-starter.version');
        $controllerName = Naming::model($actor) . "AuthController";
        return new self(
            actor: $actor,
            pathname: "user-details",
            controllerName: $controllerName,
            actionName: "userDetails",
            method: "get",
            name: "$version.api.$actor.user.details",
            viewName: null
        );
    }

    public static function webLogin(): RouteString
    {
        $version = config('cubeta-starter.version');
        return new self(
            actor: "dashboard",
            pathname: "login",
            controllerName: "BaseAuthController",
            actionName: "login",
            method: "get",
            name: "$version.web.public.login",
            viewName: null
        );
    }

    public static function webLoginPage(): RouteString
    {
        $version = config('cubeta-starter.version');
        $frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        return new self(
            actor: "dashboard",
            pathname: "login",
            controllerName: null,
            actionName: null,
            method: $frontType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: "$version.web.public.login",
            viewName: $frontType == FrontendTypeEnum::REACT_TS ? 'auth/Login' : 'login'
        );
    }

    public static function webRegister(): RouteString
    {
        $version = config('cubeta-starter.version');
        return new self(
            actor: "dashboard",
            pathname: "register",
            controllerName: "BaseAuthController",
            actionName: "register",
            method: "post",
            name: "$version.web.public.register",
            viewName: null
        );
    }

    public static function webRegisterPage(): RouteString
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
            viewName: $frontType == FrontendTypeEnum::REACT_TS ? 'auth/Register' : 'register'
        );
    }

    public static function webPasswordResetRequest(): RouteString
    {
        $version = config('cubeta-starter.version');
        return new self(
            actor: "dashboard",
            pathname: "request-reset-password-code",
            controllerName: "BaseAuthController",
            actionName: "requestResetPasswordCode",
            method: "post",
            name: "$version.web.public.request.reset.password.code",
            viewName: null
        );
    }

    public static function webPasswordResetRequestPage(): RouteString
    {
        $version = config('cubeta-starter.version');
        $frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        return new self(
            actor: "dashboard",
            pathname: "request-reset-password-code",
            controllerName: null,
            actionName: null,
            method: $frontType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: "$version.web.public.request.reset.password.code.page",
            viewName: $frontType == FrontendTypeEnum::REACT_TS ? 'auth/ForgetPassword' : 'reset-password-request'
        );
    }

    public static function webPasswordReset(): RouteString
    {
        $version = config('cubeta-starter.version');
        return new self(
            actor: "dashboard",
            pathname: "change-password",
            controllerName: "BaseAuthController",
            actionName: "changePassword",
            method: "post",
            name: "$version.web.public.change.password",
            viewName: null
        );
    }

    public static function webPasswordResetPage(): RouteString
    {
        $version = config('cubeta-starter.version');
        $frontType = Settings::make()->getFrontendType() ?? FrontendTypeEnum::NONE;

        return new self(
            actor: "dashboard",
            pathname: "change-password",
            controllerName: null,
            actionName: null,
            method: $frontType == FrontendTypeEnum::REACT_TS ? "inertia" : "view",
            name: "$version.web.public.change.password.page",
            viewName: $frontType == FrontendTypeEnum::REACT_TS ? 'auth/ResetPassword' : 'reset-password'
        );
    }

    public static function webUpdateUser(): RouteString
    {
        $version = config('cubeta-starter.version');
        return new self(
            actor: "dashboard",
            pathname: "update-user-data",
            controllerName: "BaseAuthController",
            actionName: "updateUserData",
            method: "put",
            name: "$version.web.protected.update.user.data",
            viewName: null
        );
    }

    public static function webUserDetails(): RouteString
    {
        $version = config('cubeta-starter.version');
        return new self(
            actor: "dashboard",
            pathname: "user-details",
            controllerName: "BaseAuthController",
            actionName: "userDetails",
            method: "get",
            name: "$version.web.protected.user.details",
            viewName: null
        );
    }

    public static function webLogout(): RouteString
    {
        $version = config('cubeta-starter.version');
        return new self(
            actor: "dashboard",
            pathname: "logout",
            controllerName: "BaseAuthController",
            actionName: "logout",
            method: "get",
            name: "$version.web.protected.logout",
            viewName: null
        );
    }
}