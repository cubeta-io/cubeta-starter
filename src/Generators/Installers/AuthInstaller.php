<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\EnvParser;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeInfo;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\CubeWarning;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class AuthInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-auth";

    public string $type = 'installer';

    private array $publicRoutes = [];
    private array $protectedRoutes = [];

    public function __construct(string $fileName = "", array $attributes = [], array $relations = [], array $nullables = [], array $uniques = [], ?string $actor = null, string $generatedFor = '', string $version = 'v1')
    {
        parent::__construct($fileName, $attributes, $relations, $nullables, $uniques, $actor, $generatedFor, $version);
        $this->publicRoutes = $this->getAuthRouteNames($this->generatedFor, $this->actor, true);
        $this->protectedRoutes = $this->getAuthRouteNames($this->generatedFor, $this->actor);
    }

    /**
     * @param bool $override
     * @return void
     */
    public function run(bool $override = true): void
    {
        $envParser = EnvParser::make();

        $this->generateUserMigration($override);
        $this->generateUserModel($override);
        $this->generateUserService($override);
        $this->generateUserRepository($override);
        $this->generateAuthRequests($override);
        $this->generateResetPasswordNotification($override);
        $this->generateUserFactory($override);
        $this->generateResetPasswordEmail($override);

        if (ContainerType::isApi($this->generatedFor)) {
            $this->initializeJwt();
            $this->generateUserResource($override);
            $this->generateBaseAuthApiController($override);
        }

        if (ContainerType::isWeb($this->generatedFor)) {
            $this->generateBaseAuthWebController($override);
            $this->generateWebAuthRoutes();
            $this->generateAuthViews($override);

            if ($this->frontType == FrontendTypeEnum::REACT_TS) {
                $this->addRouteToReactTsProfileDropdown("logout", $this->protectedRoutes['logout']);
                $this->addRouteToReactTsProfileDropdown("user-details", $this->protectedRoutes['user-details']);
            } else {
                $this->addRouteToBladeNavbarDropdown("user-details", $this->protectedRoutes['user-details']);
                $this->addRouteToBladeNavbarDropdown("logout", $this->protectedRoutes['logout']);
            }

            if ($envParser && !$envParser->hasValue("APP_KEY")) {
                FileUtils::executeCommandInTheBaseDirectory("php artisan key:generate");
            }
        }

        CubeLog::add(new CubeWarning("Don't forgot to re-run your users table migration"));

        Settings::make()->setInstalledAuth();
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserMigration(bool $override = false): void
    {
        $migrationPath = CubePath::make(config('cubeta-starter.migration_path') . '/0001_01_01_000000_create_users_table.php');

        $this->generateFileFromStub(
            [],
            $migrationPath->fullPath,
            $override,
            CubePath::stubPath('Auth/UserMigration.stub'),
        );
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserModel(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.model_namespace'),
            '{hasRoles}'  => Settings::make()->installedRoles() ? "use \App\Traits\HasRoles;\n" : '',
        ];

        $modelPath = CubePath::make(config('cubeta-starter.model_path') . "/User.php");

        $modelPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $modelPath->fullPath,
            $override,
            CubePath::stubPath('Auth/UserModel.stub')
        );
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserService(bool $override = false): void
    {
        // user service
        $stubProperties = [
            '{{namespace}}'           => config('cubeta-starter.service_namespace') . "\\$this->version",
            '{{modelNamespace}}'      => config('cubeta-starter.model_namespace'),
            '{{repositoryNamespace}}' => config('cubeta-starter.repository_namespace'),
        ];


        $servicePath = CubePath::make(config('cubeta-starter.service_path') . "/{$this->version}/User/UserService.php");

        $servicePath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $servicePath->fullPath,
            $override,
            CubePath::stubPath('Auth/UserService.stub')
        );
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserRepository(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}'      => config('cubeta-starter.repository_namespace'),
            '{modelNamespace}' => config('cubeta-starter.model_namespace'),
        ];

        $repositoryPath = CubePath::make(config('cubeta-starter.repository_path') . "/UserRepository.php");

        $repositoryPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $repositoryPath->fullPath,
            $override,
            CubePath::stubPath('Auth/UserRepository.stub')
        );
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateAuthRequests(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.request_namespace') . "\\$this->version",
        ];

        $requestDirectory = base_path(config('cubeta-starter.request_path') . "/$this->version/AuthRequests");

        FileUtils::ensureDirectoryExists($requestDirectory);

        $this->generateFileFromStub($stubProperties, CubePath::make("{$requestDirectory}/AuthLoginRequest.php")->fullPath, $override, CubePath::stubPath('Auth/AuthRequests/AuthLoginRequest.stub'));
        $this->generateFileFromStub($stubProperties, CubePath::make("{$requestDirectory}/AuthRegisterRequest.php")->fullPath, $override, CubePath::stubPath('Auth/AuthRequests/AuthRegisterRequest.stub'));
        $this->generateFileFromStub($stubProperties, CubePath::make("{$requestDirectory}/CheckPasswordResetRequest.php")->fullPath, $override, CubePath::stubPath('Auth/AuthRequests/CheckPasswordResetRequest.stub'));
        $this->generateFileFromStub($stubProperties, CubePath::make("{$requestDirectory}/RequestResetPasswordRequest.php")->fullPath, $override, CubePath::stubPath('Auth/AuthRequests/RequestResetPasswordRequest.stub'));
        $this->generateFileFromStub($stubProperties, CubePath::make("{$requestDirectory}/ResetPasswordRequest.php")->fullPath, $override, CubePath::stubPath('Auth/AuthRequests/ResetPasswordRequest.stub'));
        $this->generateFileFromStub($stubProperties, CubePath::make("{$requestDirectory}/UpdateUserRequest.php")->fullPath, $override, CubePath::stubPath('Auth/AuthRequests/UpdateUserRequest.stub'));
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateResetPasswordNotification(bool $override = false): void
    {
        $notificationPath = CubePath::make("app/Notifications/ResetPasswordCodeEmail.php");
        $notificationPath->ensureDirectoryExists();

        $this->generateFileFromStub([], $notificationPath->fullPath, $override, CubePath::stubPath('Auth/ResetPasswordCodeEmail.stub'));
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateAuthViews(bool $override = false): void
    {
        if (!ContainerType::isWeb($this->generatedFor)) {
            return;
        }

        $generationInfo = [];
        $stubProperties = [
            'login-page'                  => [
                "{{login-route}}"                 => $this->publicRoutes['login'],
                "{{register-page}}"               => $this->publicRoutes['register-page'],
                "{{password-reset-request-page}}" => $this->publicRoutes['password-reset-request-page'],
            ],
            'register-page'               => [
                "{{register-route}}"   => $this->publicRoutes['register'],
                "{{login-page-route}}" => $this->publicRoutes['login-page'],
            ],
            'user-details-page'           => ["{{update-user-data-route}}" => $this->protectedRoutes['update-user-details']],
            'reset-password-request-page' => ["{{password-reset-request}}" => $this->publicRoutes['password-reset-request']],
            'validate-reset-code-page'    => ["{{validate-reset-code}}" => $this->publicRoutes['validate-reset-code']],
            'reset-password-page'         => ["{{password-reset}}" => $this->publicRoutes['password-reset']],
        ];
        if ($this->frontType == FrontendTypeEnum::REACT_TS) {
            $generationInfo[] = [
                'stub_properties' => $stubProperties['login-page'],
                'stub_path'       => CubePath::stubPath('Inertia/pages/auth/Login.stub'),
                'target'          => CubePath::make('resources/js/Pages/auth/Login.tsx')->fullPath,
            ];
            $generationInfo[] = [
                'stub_properties' => $stubProperties['register-page'],
                'stub_path'       => CubePath::stubPath('Inertia/pages/auth/Register.stub'),
                'target'          => CubePath::make('resources/js/Pages/auth/Register.tsx')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['user-details-page'],
                'stub_path'       => CubePath::stubPath('Inertia/pages/auth/UserDetails.stub'),
                'target'          => CubePath::make('resources/js/Pages/dashboard/profile/UserDetails.tsx')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['reset-password-request-page'],
                'stub_path'       => CubePath::stubPath('Inertia/pages/auth/ForgetPassword.stub'),
                'target'          => CubePath::make('resources/js/Pages/auth/ForgetPassword.tsx')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['validate-reset-code-page'],
                'stub_path'       => CubePath::stubPath('Inertia/pages/auth/ResetPasswordCodeForm.stub'),
                'target'          => CubePath::make('resources/js/Pages/auth/ResetPasswordCodeForm.tsx')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['reset-password-page'],
                'stub_path'       => CubePath::stubPath('Inertia/pages/auth/ResetPassword.stub'),
                'target'          => CubePath::make('resources/js/Pages/auth/ResetPassword.tsx')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => [],
                'stub_path'       => CubePath::stubPath('Inertia/pages/auth/User.stub'),
                'target'          => CubePath::make('resources/js/Models/User.ts')->fullPath,
            ];
        } elseif ($this->frontType == FrontendTypeEnum::BLADE) {
            $generationInfo[] = [
                'stub_properties' => [
                    "{{login-route}}"                 => $this->publicRoutes['login'],
                    "{{register-page}}"               => $this->publicRoutes['register-page'],
                    "{{password-reset-request-page}}" => $this->publicRoutes['password-reset-request-page'],
                ],
                'stub_path'       => CubePath::stubPath('Auth/views/login.blade.stub'),
                'target'          => CubePath::make('resources/views/login.blade.php')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['register-page'],
                'stub_path'       => CubePath::stubPath('Auth/views/register.blade.stub'),
                'target'          => CubePath::make('resources/views/register.blade.php')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['user-details-page'],
                'stub_path'       => CubePath::stubPath('Auth/views/user-details.blade.stub'),
                'target'          => CubePath::make('resources/views/user-details.blade.php')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['reset-password-request-page'],
                'stub_path'       => CubePath::stubPath('Auth/views/reset-password-request.blade.stub'),
                'target'          => CubePath::make('resources/views/reset-password-request.blade.php')->fullPath,
            ];

            $generationInfo[] = [
                'stub_properties' => $stubProperties['validate-reset-code-page'],
                'stub_path'       => CubePath::stubPath('Auth/views/check-reset-code.blade.stub'),
                'target'          => CubePath::make('resources/views/check-reset-code.blade.php')->fullPath,
            ];
            $generationInfo[] = [
                'stub_properties' => $stubProperties['reset-password-page'],
                'stub_path'       => CubePath::stubPath('Auth/views/reset-password.blade.stub'),
                'target'          => CubePath::make('resources/views/reset-password.blade.php')->fullPath,
            ];
        }

        foreach ($generationInfo as $info) {
            $this->generateFileFromStub(
                $info['stub_properties'],
                $info['target'],
                $override,
                $info['stub_path']
            );
        }
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserFactory(bool $override = false): void
    {
        $factoryPath = CubePath::make(config('cubeta-starter.factory_path') . "/UserFactory.php");
        $factoryPath->ensureDirectoryExists();

        $this->generateFileFromStub([], $factoryPath->fullPath, $override, CubePath::stubPath('Auth/user-factory.stub'));
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateUserResource(bool $override = false): void
    {
        $stubProperties = [
            '{namespace}'         => config('cubeta-starter.resource_namespace') . "\\$this->version",
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
        ];

        $resourcePath = CubePath::make(config('cubeta-starter.resource_path') . "/$this->version/UserResource.php");

        $resourcePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $resourcePath->fullPath, $override, CubePath::stubPath('Auth/UserResource.stub'));
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateBaseAuthApiController(bool $override = false): void
    {
        $stubProperties = [
            '{{namespace}}'         => config('cubeta-starter.api_controller_namespace') . "\\$this->version",
            '{{requestNamespace}}'  => config('cubeta-starter.request_namespace') . "\\$this->version",
            '{{serviceNamespace}}'  => config('cubeta-starter.service_namespace') . "\\$this->version",
            '{{resourceNamespace}}' => config('cubeta-starter.resource_namespace') . "\\$this->version",
        ];

        $controllerPath = CubePath::make(config('cubeta-starter.api_controller_path') . "/$this->version/BaseAuthController.php");

        $controllerPath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath, $override, CubePath::stubPath('Auth/BaseAuthController.stub'));
    }

    /**
     * @param bool $override
     * @return void
     */
    private function generateBaseAuthWebController(bool $override = false): void
    {
        $stubProperties = [
            '{{namespace}}'           => config('cubeta-starter.web_controller_namespace') . "\\$this->version",
            '{{requestNamespace}}'    => config('cubeta-starter.request_namespace') . "\\$this->version",
            '{{serviceNamespace}}'    => config('cubeta-starter.service_namespace') . "\\$this->version",
            "{{user-details-route}}"  => $this->protectedRoutes['user-details'],
            "{{password-reset-page}}" => $this->publicRoutes['password-reset-page'],
            "{{login-page-route}}"    => $this->publicRoutes['login-page'],
        ];

        $controllerPath = CubePath::make(config('cubeta-starter.web_controller_path') . "/$this->version/BaseAuthController.php");

        $controllerPath->ensureDirectoryExists();

        if ($this->frontType == FrontendTypeEnum::REACT_TS) {
            $stubPath = CubePath::stubPath('Auth/BaseAuthReactTsController.stub');
        } else {
            $stubPath = CubePath::stubPath('Auth/BaseAuthBladeController.stub');
        }

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath, $override, $stubPath);
    }

    /**
     * @return void
     */
    private function generateWebAuthRoutes(): void
    {
        $publicRoutesStubProperties = [
            '{{version}}'                          => $this->version,
            "{{login-page-route}}"                 => $this->publicRoutes['login-page'],
            "{{login-route}}"                      => $this->publicRoutes['login'],
            "{{request-password-reset-code-page}}" => $this->publicRoutes['password-reset-request-page'],
            "{{request-password-reset-code}}"      => $this->publicRoutes['password-reset-request'],
            "{{validate-password-reset-code}}"     => $this->publicRoutes['validate-reset-code'],
            "{{password-reset-page}}"              => $this->publicRoutes['password-reset-page'],
            "{{password-reset}}"                   => $this->publicRoutes['password-reset'],
            "{{register-page}}"                    => $this->publicRoutes['register-page'],
            "{{register}}"                         => $this->publicRoutes['register'],
        ];

        $protectedRoutesStubProperties = [
            "{{version}}"             => $this->version,
            "{{update-user-details}}" => $this->protectedRoutes['update-user-details'],
            "{{user-details-route}}"  => $this->protectedRoutes['user-details'],
            "{{logout-route}}"        => $this->protectedRoutes['logout'],
        ];


        if ($this->frontType == FrontendTypeEnum::REACT_TS) {
            $protectedRoutes = FileUtils::generateStringFromStub(
                CubePath::stubPath('Auth/auth-react-ts-routes-protected.stub'),
                $protectedRoutesStubProperties
            );
            $publicRoutes = FileUtils::generateStringFromStub(
                CubePath::stubPath('Auth/auth-react-ts-routes-public.stub'),
                $publicRoutesStubProperties
            );
        } else {
            $protectedRoutes = FileUtils::generateStringFromStub(
                CubePath::stubPath('Auth/auth-web-routes-protected.stub'),
                $protectedRoutesStubProperties
            );
            $publicRoutes = FileUtils::generateStringFromStub(
                CubePath::stubPath('Auth/auth-web-routes-public.stub'),
                $publicRoutesStubProperties
            );
        }

        $publicRouteFile = CubePath::make("routes/{$this->version}/web/public.php");
        $protectedRouteFile = CubePath::make("routes/{$this->version}/web/protected.php");

        $publicRouteFile->ensureDirectoryExists();
        $protectedRouteFile->ensureDirectoryExists();

        $importStatement = "use " . config('cubeta-starter.web_controller_namespace') . "\\$this->version;";

        if (!$publicRouteFile->exist()) {
            $this->generateFileFromStub(
                ["{route}" => $publicRoutes, '{version}' => $this->version],
                $publicRouteFile->fullPath,
                true,
                CubePath::stubPath('api.stub')
            );
            $this->registerRouteFile($publicRouteFile, ContainerType::WEB);
            FileUtils::addImportStatement($importStatement, $publicRouteFile);
            CubeLog::add(new SuccessMessage("Auth Web Public Routes Has Been Added Successfully To : [{$publicRouteFile->fullPath}]"));
        } else {
            $publicRoutes = explode(";", $publicRoutes);
        }

        if (!$protectedRouteFile->exist()) {
            $this->generateFileFromStub(
                ["{route}" => $protectedRoutes, '{version}' => $this->version],
                $protectedRouteFile->fullPath,
                true,
                CubePath::stubPath('api.stub')
            );
            $this->registerRouteFile($protectedRouteFile, ContainerType::WEB);
            FileUtils::addImportStatement($importStatement, $protectedRouteFile);
            CubeLog::add(new SuccessMessage("Auth Web Protected Routes Has Been Added Successfully To : [{$protectedRouteFile->fullPath}]"));
        } else {
            $protectedRoutes = explode(";", $protectedRoutes);
        }

        if (is_array($protectedRoutes)) {
            foreach ($protectedRoutes as $protectedRoute) {
                if (!FileUtils::contentExistInFile($protectedRouteFile, $protectedRoute)) {
                    $protectedRouteFile->putContent("$protectedRoute;", FILE_APPEND);
                    FileUtils::addImportStatement($importStatement, $protectedRouteFile);
                    CubeLog::add(new ContentAppended($protectedRoute, $protectedRouteFile->fullPath));
                }
            }
        }

        if (is_array($publicRoutes)) {
            foreach ($publicRoutes as $publicRoute) {
                if (!FileUtils::contentExistInFile($publicRouteFile, $publicRoute)) {
                    $publicRouteFile->putContent("$publicRoute;", FILE_APPEND);
                    FileUtils::addImportStatement($importStatement, $publicRouteFile);
                    CubeLog::add(new ContentAppended($publicRoute, $publicRouteFile->fullPath));
                }
            }
        }
    }

    /**
     * @param bool $override
     * @return void
     */
    public function generateResetPasswordEmail(bool $override): void
    {
        $viewsPath = CubePath::make("resources/views/emails/reset-password-email.blade.php");
        $viewsPath->ensureDirectoryExists();
        $this->generateFileFromStub([], $viewsPath->fullPath, $override, CubePath::stubPath('Auth/reset-password-email.stub'));
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
            CubeLog::add(new NotFound($navbarPath->fullPath, "Adding auth routes to navbar dropdown"));
            return;
        }

        $navbarContent = $navbarPath->getContent();
        $pattern = '#<\s*\ba\s*(.*?)\s*\bid=[\'"]\b' . $tagId . '[\'"]\s*(.*?)\s*>#';
        $navbarContent = preg_replace_callback($pattern, function ($matches) use ($navbarPath, $routeName, $tagId) {
            $hrefPattern = '/href\s*=\s*[\'"](.*?)[\'"]/s';
            if (isset($matches[0]) && preg_match($hrefPattern, $matches[0])) {
                CubeLog::add(new ContentAppended("href=\"{{route('$routeName')}}\"", $navbarPath->fullPath));
                return preg_replace_callback($hrefPattern, function () use ($routeName) {
                    return "href=\"{{route('$routeName')}}\"";
                }, $matches[0]);
            } else {
                CubeLog::add(new ContentAppended("href=\"{{route('$routeName')}}\"", $navbarPath->fullPath));
                return "<a $matches[1] id=\"$tagId\" $matches[2] href=\"{{route('$routeName')}}\"";
            }
        }, $navbarContent);
        $navbarPath->putContent($navbarContent);
    }

    public function addRouteToReactTsProfileDropdown(string $tagId, string $routeName): void
    {
        $dropDownPath = CubePath::make("resources/js/Components/ui/ProfileDropDown.tsx");
        if (!$dropDownPath->exist()) {
            CubeLog::add(new NotFound($dropDownPath->fullPath, "Adding auth routes to navbar dropdown"));
            return;
        }
        $dropDownContent = $dropDownPath->getContent();
        $idTagPattern = FileUtils::getReactComponentPropPatterns('id', $tagId);
        $pattern = '#<\s*Link\s*[^>]*\s*' . $idTagPattern . '\s*[^>]*\s*>#s';

        if (preg_match($pattern, $dropDownContent, $matches)) {
            $linkTag = $matches[0];
            $hrefPattern = '#' . FileUtils::getReactComponentPropPatterns('href') . '#s';
            if (preg_match($hrefPattern, $linkTag, $hrefMatches)) {
                $linkTag = preg_replace_callback($hrefPattern, function () use ($routeName) {
                    return " href={route('$routeName')} ";
                }, $linkTag);
                $dropDownContent = str_replace($matches[0], $linkTag, $dropDownContent);
                $dropDownPath->putContent($dropDownContent);
                CubeLog::add(new ContentAppended("route('$routeName')", $dropDownPath->fullPath));
                return;
            } else {
                $dropDownContent = str_replace($linkTag, str_replace('>', " href={route('$routeName')} >", $linkTag), $dropDownContent);
                $dropDownPath->putContent($dropDownContent);
                CubeLog::add(new ContentAppended("href={route('$routeName')}", $dropDownPath->fullPath));
                return;
            }
        }

        CubeLog::add(new FailedAppendContent("href={route('$routeName')}", $dropDownPath->fullPath), "Adding auth routes to navbar dropdown");
    }

    private function initializeJwt(): void
    {
        $envParser = EnvParser::make();
        FileUtils::executeCommandInTheBaseDirectory("composer require php-open-source-saver/jwt-auth");
        $this->addJwtGuard();
        $envParser?->addVariable("JWT_ALGO", "HS256");
        if ($envParser && !$envParser->hasValue("JWT_SECRET")) {
            FileUtils::executeCommandInTheBaseDirectory("php artisan jwt:secret");
        }
        $envParser?->addVariable("JWT_BLACKLIST_ENABLED", "false");
    }
}
