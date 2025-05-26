<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Logs\CubeInfo;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Postman\Postman;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Api\Controllers\RoleAuthControllerStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Api\Routes\RoleProtectedAuthRoutesStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Api\Routes\RolePublicAuthRoutesStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Seeders\RoleSeederStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;
use Illuminate\Support\Str;

class ActorFilesGenerator extends AbstractGenerator
{
    use RouteBinding;

    public string $type = 'installer';

    private string $role;
    private ?array $permissions;
    private bool $authenticated;

    public function __construct(string $role, ?array $permissions = null, bool $authenticated = false, string $generatedFor = ContainerType::API, string $version = 'v1', bool $override = false)
    {
        $this->role = Naming::role($role);
        $this->actor = $this->role;
        $this->permissions = $permissions;
        $this->authenticated = $authenticated;
        parent::__construct(actor: $this->role, generatedFor: $generatedFor, version: $version, override: $override);
    }

    public function run(): void
    {
        $settings = Settings::make();
        if (!$settings->installedRoles()) {
            CubeLog::error("Install permissions by running [php artisan cubeta:install permissions] then try again");
            return;
        }
        if (
            (!$settings->installedApiAuth() && !$settings->installedWebAuth())
            || (!$settings->installedWebAuth() && ContainerType::isWeb($this->generatedFor))
            || (!$settings->installedApiAuth() && ContainerType::isApi($this->generatedFor))
        ) {
            CubeLog::error("Install auth tools by running [php artisan cubeta:install auth] then try again");
            return;
        }

        $this->createRolesEnum();
        if (ContainerType::isWeb($this->generatedFor)) {
            $routeFile = CubePath::make("routes/{$this->version}/web/{$this->role}.php");
            if (!$routeFile->exist()) {
                $this->addRouteFile($this->role, ContainerType::WEB, $this->version, [
                    'authenticated',
                    'has-role:' . $this->role,
                ]);
            }
        }

        if (ContainerType::isApi($this->generatedFor)) {
            $routeFile = CubePath::make("routes/{$this->version}/api/{$this->role}.php");
            if (!$routeFile->exist()) {
                $this->addRouteFile($this->role, ContainerType::API, $this->version, [
                    'authenticated',
                    'has-role:' . $this->role,
                    'jwt-auth',
                ]);
            }
        }

        $this->createRoleSeeder();

        if ($this->authenticated && ContainerType::isApi($this->generatedFor)) {
            $this->generateAuthControllers();
            $this->generatePublicAuthRoutes();
            $this->generateProtectedAuthRoutes();
        }

        CubeLog::add(new CubeInfo("Don't forget to run [php artisan db:seed RoleSeeder]"));
    }

    public function createRolesEnum(): void
    {
        $enum = file_get_contents(CubePath::stubPath('RolesPermissionEnum-entity.stub'));
        $roleEnum = $this->roleEnumNaming($this->role);
        $roleEnumValue = Str::singular(Str::lower($this->role));

        if ($this->permissions) {
            for ($i = 0; $i < count($this->permissions); $i++) {
                $this->permissions[$i] = Str::lower($this->permissions[$i]);
            }
        }

        $placedPermission = $this->permissions ? json_encode($this->permissions, JSON_PRETTY_PRINT) : '[]';

        $enum = str_replace(
            ['{enum}', '{roleValue}', '{permissions}'],
            [$roleEnum, $roleEnumValue, $placedPermission],
            $enum
        );

        $enumPath = CubePath::make("/app/Enums/RolesPermissionEnum.php");

        if ($enumPath->exist()) {
            $enumFileContent = $enumPath->getContent();
            if (!str_contains($enumFileContent, $this->role)) {
                // If the new code does not exist, add it to the end of the class definition
                $pattern = '/}\s*$/';
                $replacement = "{$enum}}";

                $enumFileContent = preg_replace($pattern, $replacement, $enumFileContent, 1);
                $enumFileContent = str_replace(
                    [
                        '//add-your-roles',
                        '//add-all-your-enums-roles-here',
                        '//add-all-your-enums-here',
                    ],
                    [
                        $enum,
                        'self::' . $roleEnum . "['role'], \n //add-all-your-enums-roles-here \n",
                        'self::' . $roleEnum . ", \n //add-all-your-enums-here \n",
                    ],
                    $enumFileContent
                );

                // Write the modified contents back to the file
                $enumPath->putContent($enumFileContent);
            } else {
                CubeLog::add(new ContentAlreadyExist("The Role ({$this->role})", $enumPath->fullPath, "Adding New Role Enum To RolesPermissions Enum"));
                return;
            }
        } else {
            $enumStub = file_get_contents(CubePath::stubPath('RolesPermissionEnum.stub'));

            $enumStub = str_replace(
                [
                    '//add-your-roles',
                    '//add-all-your-enums-roles-here',
                    '//add-all-your-enums-here',
                ],
                [
                    $enum,
                    'self::' . $roleEnum . "['role'], \n //add-all-your-enums-roles-here \n",
                    'self::' . $roleEnum . ", \n //add-all-your-enums-here \n",
                ],
                $enumStub
            );
            $enumPath->ensureDirectoryExists();
            $enumPath->putContent($enumStub);
        }
        $enumPath->format();

        CubeLog::add(new ContentAppended("The Role ($this->role) Enum Declaration", $enumPath->fullPath));
    }

    /**
     * return the role enum for a given string
     * @param string $name
     * @return string
     */
    public function roleEnumNaming(string $name): string
    {
        return Str::singular(Str::upper(Str::snake($name)));
    }

    public function createRoleSeeder(): void
    {
        $seederPath = CubePath::make(config('cubeta-starter.seeder_path') . '/RoleSeeder.php');

        RoleSeederStubBuilder::make()
            ->generate($seederPath, $this->override);
    }

    private function generateAuthControllers(): void
    {
        $apiControllerNamespace = config('cubeta-starter.api_controller_namespace');
        $apiServiceNamespace = config('cubeta-starter.service_namespace');
        $controllerPath = CubePath::make(config('cubeta-starter.api_controller_path') . "/$this->version/" . ucfirst(Str::studly($this->role)) . "AuthController.php");

        RoleAuthControllerStubBuilder::make()
            ->namespace("$apiControllerNamespace\\$this->version")
            ->serviceNamespace("$apiServiceNamespace\\$this->version")
            ->role($this->role)
            ->roleEnumName($this->roleEnumNaming($this->role))
            ->generate($controllerPath, $this->override);
        try {
            Postman::make()->getCollection()->newAuthApi($this->role)->save();
            CubeLog::success("Postman Collection Now Has Folder For The Generated {$this->role} Auth Controller  \nRe-Import It In Postman");
        } catch (Exception $e) {
            CubeLog::add($e);
        }
    }

    private function generateProtectedAuthRoutes(): void
    {
        $apiRouteFile = CubePath::make("routes/{$this->version}/api/{$this->actorFileNaming($this->role)}.php");
        $routes = RoleProtectedAuthRoutesStubBuilder::make()
            ->version($this->version)
            ->role($this->role)
            ->controllerName(str($this->role)->studly()->singular()->toString())
            ->refreshRouteName(Routes::refreshToken($this->role)->name)
            ->logoutRouteName(Routes::logout(ContainerType::API, $this->role)->name)
            ->updateUserRouteName(Routes::updateUser(ContainerType::API, $this->role)->name)
            ->userDetailsRouteName(Routes::me(ContainerType::API, $this->role)->name)
            ->toString();

        $apiRouteFile->putContent($routes, FILE_APPEND);
        CubeLog::contentAppended($routes, $apiRouteFile->fullPath);

        $importStatement = "use " . config('cubeta-starter.api_controller_namespace') . "\\$this->version;";
        FileUtils::addImportStatement($importStatement, $apiRouteFile);
    }

    private function generatePublicAuthRoutes(): void
    {
        $importStatement = new PhpImportString(config('cubeta-starter.api_controller_namespace') . "\\$this->version;");
        $publicApiRouteFile = CubePath::make("/routes/{$this->version}/api/public.php");

        $publicAuthRoutes = RolePublicAuthRoutesStubBuilder::make()
            ->version($this->version)
            ->role($this->role)
            ->controllerName(str($this->role)->singular()->studly()->toString())
            ->registerRouteName(Routes::register(ContainerType::API, $this->role)->name)
            ->loginRouteName(Routes::login(ContainerType::API, $this->role)->name)
            ->passwordResetRequestRouteName(Routes::requestResetPassword(ContainerType::API, $this->role)->name)
            ->validatePasswordResetCodeRouteName(Routes::validateResetCode(ContainerType::API, $this->role)->name)
            ->passwordResetRouteName(Routes::resetPassword(ContainerType::API, $this->role)->name)
            ->toString();

        if (!$publicApiRouteFile->exist()) {
            $this->addRouteFile(actor: 'public', version: $this->version);
        }

        $publicApiRouteFile->putContent($publicAuthRoutes, FILE_APPEND);
        CubeLog::contentAppended($publicAuthRoutes, $publicApiRouteFile->fullPath);

        FileUtils::addImportStatement($importStatement, $publicApiRouteFile);
    }
}
