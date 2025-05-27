<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\EnvParser;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeInfo;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Modules\Views;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\TraitString;
use Cubeta\CubetaStarter\Stub\Builders\Api\Controllers\BaseAuthControllerStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Factories\UserFactoryStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Mails\ResetPasswordCodeEmailStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Migrations\UserMigrationStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Models\UserModelStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Repositories\UserRepositoryStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Requests\AuthLoginRequestStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Requests\AuthRegisterRequestStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Requests\CheckPasswordResetRequestStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Requests\RequestResetPasswordRequestStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Requests\ResetPasswordRequestStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Requests\UpdateUserRequestStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Resources\UserResourceStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Routes\RoutesFileStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Services\UserServiceStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Controllers\BaseAuthControllerStubBuilder as BladeBaseAuthControllerStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\ForgetPasswordPageStubBuilder as BladeForgetPasswordPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\LoginPageStubBuilder as BladeLoginPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\RegisterPageStubBuilder as BladeRegisterPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\ResetPasswordCodeFormPageStubBuilder as BladeResetPasswordCodeFormPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\ResetPasswordPageStubBuilder as BladeResetPasswordPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\UserDetailsPageStubBuilder as BladeUserDetailsPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Controllers\BaseAuthControllerStubBuilder as ReactTsBaseAuthControllerStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\ForgetPasswordPageStubBuilder as ReactTsForgetPasswordPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\LoginPageStubBuilder as ReactTsLoginPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\RegisterPageStubBuilder as ReactTsRegisterPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\ResetPasswordCodeFormPageStubBuilder as ReactTsResetPasswordCodeFormPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\ResetPasswordPageStubBuilder as ReactTsResetPasswordPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\UserDetailsPageStubBuilder as ReactTsUserDetailsPageStubBuilderAlias;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Typescript\UserModelInterfaceStubBuilder;
use Cubeta\CubetaStarter\Stub\Publisher;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Collection;

class AuthInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-auth";

    public string $type = 'installer';

    /**
     * @return void
     */
    public function run(): void
    {
        $envParser = EnvParser::make();

        $this->generateUserMigration();
        $this->generateUserModel();
        $this->generateUserService();
        $this->generateUserRepository();
        $this->generateAuthRequests();
        $this->generateResetPasswordMail();
        $this->generateUserFactory();

        if (ContainerType::isApi($this->generatedFor)) {
            $this->initializeJwt();
            $this->generateUserResource();
            $this->generateBaseAuthApiController();
            Settings::make()->setInstalledApiAuth();
        }

        if (ContainerType::isWeb($this->generatedFor)) {
            $this->generateBaseAuthWebController();
            $this->generateWebAuthRoutes();

            if ($this->frontType == FrontendTypeEnum::REACT_TS) {
                $this->generateReactTsAuthViews();
                $this->addRouteToReactTsProfileDropdown("logout", Routes::logout(ContainerType::WEB, null)->name);
                $this->addRouteToReactTsProfileDropdown("user-details", Routes::me(ContainerType::WEB, null)->name);
            } else {
                $this->generateBladeAuthViews();
                $this->addRouteToBladeNavbarDropdown("logout", Routes::logout(ContainerType::WEB, null)->name);
                $this->addRouteToBladeNavbarDropdown("user-details", Routes::me(ContainerType::WEB, null)->name);
            }

            if ($envParser && !$envParser->hasValue("APP_KEY")) {
                FileUtils::executeCommandInTheBaseDirectory("php artisan key:generate");
            }

            $this->moveDashboardIndexRouteToProtectedRouteFile();

            Settings::make()->setInstalledWebAuth();
        }

        CubeLog::warning("Don't forgot to re-run your users table migration");
    }

    /**
     * @return void
     */
    private function generateUserMigration(): void
    {
        $migrationPath = CubePath::make(config('cubeta-starter.migration_path') . '/0001_01_01_000000_create_users_table.php');
        UserMigrationStubBuilder::make()
            ->generate($migrationPath, $this->override);
    }

    /**
     * @return void
     */
    private function generateUserModel(): void
    {
        $modelPath = CubePath::make(config('cubeta-starter.model_path') . "/User.php");
        $getJwtClaimsMethod = new MethodString(
            name: 'getJWTCustomClaims',
            parameters: [],
            body: [
                'return [];'
            ],
            returnType: 'array'
        );

        $getJwtIdMethod = new MethodString(
            name: 'getJWTIdentifier',
            parameters: [],
            body: [
                'return $this->getKey();',
            ],
            returnType: 'string'
        );

        UserModelStubBuilder::make()
            ->namespace(config('cubeta-starter.model_namespace'))
            ->when(
                ContainerType::isApi($this->generatedFor),
                fn($builder) => $builder->method($getJwtIdMethod)
                    ->method($getJwtClaimsMethod)
                    ->implementsJwtInterface('implements JWTSubject')
                    ->import(new PhpImportString('PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject')))
            ->when(
                Settings::make()->installedRoles(),
                fn($builder) => $builder->trait(new TraitString("HasRoles", new PhpImportString("App\Traits\HasRoles")))
            )->generate($modelPath, $this->override);
    }

    /**
     * @return void
     */
    private function generateUserService(): void
    {
        $servicePath = CubePath::make(config('cubeta-starter.service_path') . "/{$this->version}/User/UserService.php");

        UserServiceStubBuilder::make()
            ->namespace(config('cubeta-starter.service_namespace') . "\\$this->version")
            ->modelNamespace(config('cubeta-starter.model_namespace'))
            ->repositoryNamespace(config('cubeta-starter.repository_namespace'))
            ->traitsNamespace(config('cubeta-starter.trait_namespace'))
            ->serviceNamespace(config('cubeta-starter.service_namespace'))
            ->generate($servicePath, $this->override);
    }

    /**
     * @return void
     */
    private function generateUserRepository(): void
    {
        $repositoryPath = CubePath::make(config('cubeta-starter.repository_path') . "/UserRepository.php");
        UserRepositoryStubBuilder::make()
            ->namespace(config('cubeta-starter.repository_namespace'))
            ->modelNamespace(config('cubeta-starter.model_namespace'))
            ->repositoryNamespace(config('cubeta-starter.repository_namespace'))
            ->generate($repositoryPath, $this->override);
    }

    /**
     * @return void
     */
    private function generateAuthRequests(): void
    {
        $namespace = config('cubeta-starter.request_namespace') . "\\$this->version";
        $requestDirectory = CubePath::make(config('cubeta-starter.request_path') . "/$this->version/AuthRequests");

        AuthLoginRequestStubBuilder::make()
            ->namespace($namespace)
            ->generate($requestDirectory->append("AuthLoginRequest.php"), $this->override);

        AuthRegisterRequestStubBuilder::make()
            ->namespace($namespace)
            ->generate($requestDirectory->append("AuthRegisterRequest.php"), $this->override);

        CheckPasswordResetRequestStubBuilder::make()
            ->namespace($namespace)
            ->generate($requestDirectory->append("CheckPasswordResetRequest.php"), $this->override);

        RequestResetPasswordRequestStubBuilder::make()
            ->namespace($namespace)
            ->generate($requestDirectory->append("RequestResetPasswordRequest.php"), $this->override);

        ResetPasswordRequestStubBuilder::make()
            ->namespace($namespace)
            ->generate($requestDirectory->append("ResetPasswordRequest.php"), $this->override);

        UpdateUserRequestStubBuilder::make()
            ->namespace($namespace)
            ->generate($requestDirectory->append("UpdateUserRequest.php"), $this->override);
    }

    /**
     * @return void
     */
    private function generateResetPasswordMail(): void
    {
        $notificationPath = CubePath::make("app/Mail/ResetPasswordCodeEmail.php");
        ResetPasswordCodeEmailStubBuilder::make()->generate($notificationPath, $this->override);
        Publisher::make()
            ->source(CubePath::stubPath('Views/ResetPasswordEmail.stub'))
            ->destination(CubePath::make('resources/views/emails/reset-password-email.blade.php'))
            ->publish($this->override);
    }

    /**
     * @return void
     */
    private function generateUserFactory(): void
    {
        $factoryPath = CubePath::make(config('cubeta-starter.factory_path') . "/UserFactory.php");
        UserFactoryStubBuilder::make()
            ->namespace(config('cubeta-starter.factory_namespace'))
            ->modelsNamespace(config('cubeta-starter.model_namespace'))
            ->generate($factoryPath, $this->override);
    }

    /**
     * @return void
     */
    private function generateUserResource(): void
    {
        $resourcePath = CubePath::make(config('cubeta-starter.resource_path') . "/$this->version/UserResource.php");
        UserResourceStubBuilder::make()
            ->modelNamespace(trim(config('cubeta-starter.model_namespace')))
            ->namespace(config('cubeta-starter.resource_namespace') . "\\$this->version")
            ->generate($resourcePath, $this->override);
    }

    /**
     * @return void
     */
    private function generateBaseAuthApiController(): void
    {
        $controllerPath = CubePath::make(config('cubeta-starter.api_controller_path') . "/$this->version/BaseAuthController.php");
        BaseAuthControllerStubBuilder::make()
            ->namespace(config('cubeta-starter.api_controller_namespace') . "\\$this->version")
            ->requestNamespace(config('cubeta-starter.request_namespace') . "\\$this->version")
            ->serviceNamespace(config('cubeta-starter.service_namespace') . "\\$this->version")
            ->resourceNamespace(config('cubeta-starter.resource_namespace') . "\\$this->version")
            ->generate($controllerPath, $this->override);
    }

    /**
     * @return void
     */
    private function generateBaseAuthWebController(): void
    {
        $controllerPath = CubePath::make(config('cubeta-starter.web_controller_path') . "/$this->version/BaseAuthController.php");


        if ($this->frontType == FrontendTypeEnum::REACT_TS) {
            $builder = ReactTsBaseAuthControllerStubBuilder::make()
                ->resourceNamespace(config('cubeta-starter.resource_namespace') . "\\$this->version");

        } else {
            $builder = BladeBaseAuthControllerStubBuilder::make();
        }

        $builder->namespace(config('cubeta-starter.web_controller_namespace') . "\\$this->version")
            ->requestNamespace(config('cubeta-starter.request_namespace') . "\\$this->version")
            ->serviceNamespace(config('cubeta-starter.service_namespace') . "\\$this->version")
            ->userDetailsRoute(Routes::me(ContainerType::WEB, null)->name)
            ->passwordResetPageRoute(Routes::resetPasswordPage()->name)
            ->loginPageRoute(Routes::loginPage()->name)
            ->resetPasswordCodeFormPageName(config('views-names.reset-password-code-form'))
            ->userDetailsPageName(config('views-names.user-details'))
            ->generate($controllerPath, $this->override);
    }

    /**
     * @return void
     */
    private function generateWebAuthRoutes(): void
    {
        $publicRoutes = Routes::webPublicAuthRoutes();
        $protectedRoutes = Routes::webProtectedAuthRoutes();
        $publicRouteFile = $this->getRouteFilePath(ContainerType::WEB, "public");
        $protectedRouteFile = $this->getRouteFilePath(ContainerType::WEB, "protected");
        $publicRouteFile->ensureDirectoryExists();
        $protectedRouteFile->ensureDirectoryExists();
        $this->generateAuthWebRouteFileOrAppendRoutes($publicRouteFile, $publicRoutes);
        $this->generateAuthWebRouteFileOrAppendRoutes($protectedRouteFile, $protectedRoutes);
    }

    private function addJwtGuard(): bool
    {
        $authConfigPath = CubePath::make('config/auth.php');

        if (!$authConfigPath->exist()) {
            CubeLog::add(new NotFound($authConfigPath->fullPath, "Registering jwt guard"));
            CubeLog::add(new CubeInfo("Don't forget to add the jwt guard in your auth.php config file"));
            return false;
        }

        $content = $authConfigPath->getContent();
        $guard = "'api' => [\n'driver' => 'jwt' ,\n'provider' => 'users',\n]";
        $pattern = '/\'guards\'\s*=>\s*\[\s*(\'(.*?)\'\s*=>\s*\[\s*(.*?)\s*]\s*(,?))*\s*]\s*/s';
        if (preg_match($pattern, $content, $matches)) {
            if (isset($matches[1])) {
                $oldGuards = $matches[1];
                if (FileUtils::contentExistsInString($oldGuards, $guard)
                    || FileUtils::contentExistsInString($oldGuards, "'api'=>['driver'=>'jwt'")) {
                    CubeLog::add(new ContentAlreadyExist($guard, $authConfigPath->fullPath, "Registering jwt guard"));
                    return false;
                }
                $content = preg_replace_callback($pattern, function () use ($oldGuards, $guard) {
                    $oldGuards .= ",\n$guard,\n";
                    $oldGuards = FileUtils::fixArrayOrObjectCommas($oldGuards);
                    return "'guards' => [\n$oldGuards\n]";
                }, $content);
                $authConfigPath->putContent($content);
                CubeLog::add(new ContentAppended($guard, $authConfigPath->fullPath));
                $authConfigPath->format();
                return true;
            } else {
                CubeLog::add(new FailedAppendContent($guard, $authConfigPath->fullPath, "Registering jwt guard"));
                return false;
            }
        }

        CubeLog::add(new FailedAppendContent($guard, $authConfigPath->fullPath, "Registering jwt guard"));
        return false;
    }

    public function addRouteToBladeNavbarDropdown(string $tagId, string $routeName): void
    {
        $navbarPath = CubePath::make("resources/views/includes/navbar.blade.php");
        if (!$navbarPath->exist()) {
            CubeLog::notFound($navbarPath->fullPath, "Adding auth routes to navbar dropdown");
            return;
        }

        $navbarContent = $navbarPath->getContent();
        $pattern = '#<\s*\ba\s*(.*?)\s*\bid=[\'"]\b' . $tagId . '[\'"]\s*(.*?)\s*>#';
        $navbarContent = preg_replace_callback($pattern, function ($matches) use ($navbarPath, $routeName, $tagId) {
            $hrefPattern = '/href\s*=\s*[\'"](.*?)[\'"]/s';
            CubeLog::contentAppended("href=\"{{route('$routeName')}}\"", $navbarPath->fullPath);
            if (isset($matches[0]) && preg_match($hrefPattern, $matches[0])) {
                return preg_replace_callback($hrefPattern, function () use ($routeName) {
                    return "href=\"{{route('$routeName')}}\"";
                }, $matches[0]);
            } else {
                return "<a $matches[1] id=\"$tagId\" $matches[2] href=\"{{route('$routeName')}}\"";
            }
        }, $navbarContent);
        $navbarPath->putContent($navbarContent);
    }

    public function addRouteToReactTsProfileDropdown(string $tagId, string $routeName): void
    {
        $dropDownPath = CubePath::make("resources/js/Components/ui/ProfileDropDown.tsx");
        if (!$dropDownPath->exist()) {
            CubeLog::notFound($dropDownPath->fullPath, "Adding auth routes to navbar dropdown");
            return;
        }
        $dropDownContent = $dropDownPath->getContent();
        $idTagPattern = FileUtils::getReactComponentPropPatterns('id', $tagId);
        $pattern = '#<\s*Link\s*[^>]*\s*' . $idTagPattern . '\s*[^>]*\s*>#s';

        if (preg_match($pattern, $dropDownContent, $matches)) {
            $linkTag = $matches[0];
            $hrefPattern = '#' . FileUtils::getReactComponentPropPatterns('href') . '#s';
            if (preg_match($hrefPattern, $linkTag)) {
                $linkTag = preg_replace_callback($hrefPattern, function () use ($routeName) {
                    return " href={route('$routeName')} ";
                }, $linkTag);
                $dropDownContent = str_replace($matches[0], $linkTag, $dropDownContent);
                $dropDownPath->putContent($dropDownContent);
                CubeLog::contentAppended("route('$routeName')", $dropDownPath->fullPath);
            } else {
                $dropDownContent = str_replace($linkTag, str_replace('>', " href={route('$routeName')} >", $linkTag), $dropDownContent);
                $dropDownPath->putContent($dropDownContent);
                CubeLog::contentAppended("href={route('$routeName')}", $dropDownPath->fullPath);
            }
            return;
        }

        CubeLog::failedAppending("href={route('$routeName')}", $dropDownPath->fullPath, "Adding auth routes to navbar dropdown");
    }

    private function initializeJwt(): void
    {
        $envParser = EnvParser::make();
        PackageManager::composerInstall("php-open-source-saver/jwt-auth");
        $this->addJwtGuard();
        $envParser?->addVariable("JWT_ALGO", "HS256");
        if ($envParser && !$envParser->hasValue("JWT_SECRET")) {
            FileUtils::executeCommandInTheBaseDirectory("php artisan jwt:secret");
        }
        $envParser?->addVariable("JWT_BLACKLIST_ENABLED", "false");

        Publisher::make()
            ->source(CubePath::stubPath('Middlewares/JWTAuthMiddleware.stub'))
            ->destination(CubePath::make('app/Http/Middleware/JWTAuthMiddleware.php'))
            ->publish($this->override);

        FileUtils::registerMiddleware(
            "'jwt-auth' => JWTAuthMiddleware::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString('App\Http\Middleware\JWTAuthMiddleware')
        );
    }

    /**
     * @param CubePath           $routeFile
     * @param Collection<Routes> $routes
     * @return void
     */
    private function generateAuthWebRouteFileOrAppendRoutes(CubePath $routeFile, Collection $routes): void
    {
        $controllerImportStatement = new PhpImportString(config('cubeta-starter.web_controller_namespace') . "\\$this->version;");
        if (!$routeFile->exist()) {
            RoutesFileStubBuilder::make()
                ->route($routes->toArray())
                ->import($controllerImportStatement)
                ->generate($routeFile, $this->override);
            $this->registerRouteFile($routeFile, ContainerType::WEB);
        } else {
            $addedRoutes = "";
            FileUtils::addImportStatement($controllerImportStatement, $routeFile);
            foreach ($routes->toArray() as $route) {
                if (!FileUtils::contentExistInFile($routeFile, $route->toString())) {
                    $addedRoutes .= $route->toString() . "\n";
                }
            }
            $routeFile->putContent($addedRoutes, FILE_APPEND);
            CubeLog::contentAppended($addedRoutes, $routeFile);
        }
    }

    /**
     * @return void
     */
    private function generateReactTsAuthViews(): void
    {
        ReactTsLoginPageStubBuilderAlias::make()
            ->loginRoute(Routes::login(ContainerType::WEB, null)->name)
            ->registerPageRoute(Routes::registerPage()->name)
            ->passwordResetRequestPageRoute(Routes::forgetPasswordPage()->name)
            ->generate(Views::login()->path, $this->override);

        ReactTsRegisterPageStubBuilderAlias::make()
            ->loginPageRoute(Routes::loginPage()->name)
            ->registerRoute(Routes::register(ContainerType::WEB, null)->name)
            ->generate(Views::register()->path, $this->override);

        ReactTsUserDetailsPageStubBuilderAlias::make()
            ->updateUserDataRoute(Routes::updateUser(ContainerType::WEB, null)->name)
            ->generate(Views::userDetails()->path, $this->override);

        ReactTsForgetPasswordPageStubBuilderAlias::make()
            ->passwordResetRequestRoute(Routes::requestResetPassword(ContainerType::WEB, null)->name)
            ->generate(Views::forgetPassword()->path, $this->override);

        ReactTsResetPasswordCodeFormPageStubBuilderAlias::make()
            ->validateResetCodeRoute(Routes::validateResetCode(ContainerType::WEB, null)->name)
            ->generate(Views::resetPasswordCodeForm()->path, $this->override);

        ReactTsResetPasswordPageStubBuilderAlias::make()
            ->passwordResetRoute(Routes::resetPassword(ContainerType::WEB, null)->name)
            ->generate(Views::resetPassword()->path, $this->override);

        UserModelInterfaceStubBuilder::make()
            ->generate(CubePath::make('resources/js/Models/User.ts'), $this->override);
    }

    /**
     * @return void
     */
    private function generateBladeAuthViews(): void
    {
        BladeLoginPageStubBuilderAlias::make()
            ->loginRoute(Routes::login(ContainerType::WEB, null)->name)
            ->registerPageRoute(Routes::registerPage()->name)
            ->passwordResetRequestPageRoute(Routes::forgetPasswordPage()->name)
            ->generate(CubePath::make('resources/views/' . config('views-names.login') . '.blade.php'), $this->override);

        BladeRegisterPageStubBuilderAlias::make()
            ->registerRoute(Routes::register(ContainerType::WEB, null)->name)
            ->loginPageRoute(Routes::loginPage()->name)
            ->generate(CubePath::make('resources/views/' . config('views-names.register') . '.blade.php'), $this->override);

        BladeUserDetailsPageStubBuilderAlias::make()
            ->updateUserDataRoute(Routes::updateUser(ContainerType::WEB, null)->name)
            ->generate(CubePath::make('resources/views/' . config('views-names.user-details') . '.blade.php'), $this->override);

        BladeForgetPasswordPageStubBuilder::make()
            ->passwordResetRequestRoute(Routes::requestResetPassword(ContainerType::WEB, null)->name)
            ->generate(CubePath::make('resources/views/' . config('views-names.forget-password') . '.blade.php'), $this->override);

        BladeResetPasswordCodeFormPageStubBuilderAlias::make()
            ->validateResetCodeRoute(Routes::validateResetCode(ContainerType::WEB, null)->name)
            ->generate(CubePath::make('resources/views/' . config('views-names.reset-password-code-form') . '.blade.php'), $this->override);

        BladeResetPasswordPageStubBuilderAlias::make()
            ->passwordResetRoute(Routes::resetPassword(ContainerType::WEB, null)->name)
            ->generate(CubePath::make('resources/views/' . config('views-names.reset-password') . '.blade.php'), $this->override);
    }

    public function moveDashboardIndexRouteToProtectedRouteFile(): void
    {
        $publicRouteFile = $this->getRouteFilePath(ContainerType::WEB, "public");
        $protectedRouteFile = $this->getRouteFilePath(ContainerType::WEB, "protected");

        $publicRoute = Routes::dashboardPage();
        $protectedRoute = Routes::dashboardPage(true);

        if (FileUtils::contentExistInFile($publicRouteFile, $publicRoute->toString())) {
            $content = $publicRouteFile->getContent();
            $content = str_replace($publicRoute, "", $content);
            $publicRouteFile->putContent($content);
            $publicRouteFile->format();
            CubeLog::contentRemoved($publicRoute, $publicRouteFile);
        }

        if (!FileUtils::contentExistInFile($protectedRouteFile, $protectedRoute)) {
            $content = $protectedRouteFile->getContent();
            $content = "$content\n$protectedRoute";
            $protectedRouteFile->putContent($content);
            $protectedRouteFile->format();
            CubeLog::contentAppended($protectedRoute, $protectedRouteFile);
        }

        if (Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS) {
            $sidebarPath = CubePath::make('resources/js/Components/ui/Sidebar.tsx');
        } else {
            $sidebarPath = CubePath::make('resources/views/includes/sidebar.blade.php');
        }

        if (FileUtils::contentExistInFile($sidebarPath, $publicRoute->name)) {
            $content = $sidebarPath->getContent();
            $content = str_replace($publicRoute->name, $protectedRoute->name, $content);
            $sidebarPath->putContent($content);
            $sidebarPath->format();
            CubeLog::contentRemoved("route('$publicRoute->name')", $sidebarPath);
            CubeLog::contentAppended("route('$protectedRoute->name')", $sidebarPath);
        }
    }
}
