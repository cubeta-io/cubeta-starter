<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Postman\Postman;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeInfo;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\CubeWarning;
use Cubeta\CubetaStarter\Logs\Errors\AlreadyExist;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;
use Illuminate\Support\Str;

class ActorFilesGenerator extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "add-actor";

    public string $type = 'installer';

    private string $role;
    private ?array $permissions;
    private bool $authenticated;

    public function __construct(string $role, ?array $permissions = null, bool $authenticated = false, string $generatedFor = ContainerType::API, string $version = 'v1')
    {
        $this->role = Naming::role($role);
        $this->actor = $this->role;
        $this->permissions = $permissions;
        $this->authenticated = $authenticated;
        parent::__construct(actor: $this->role, generatedFor: $generatedFor, version: $version);
    }

    public function run(bool $override = false): void
    {
        $settings = Settings::make();
        if (!$settings->installedRoles()) {
            CubeLog::add(new CubeError("Install permissions by running [php artisan cubeta:install permissions] then try again"));
            return;
        }
        if (
            (!$settings->installedApiAuth() && !$settings->installedWebAuth())
            || (!$settings->installedWebAuth() && ContainerType::isWeb($this->generatedFor))
            || (!$settings->installedApiAuth() && ContainerType::isApi($this->generatedFor))
        ) {
            CubeLog::add(new CubeError("Install auth tools by running [php artisan cubeta:install auth] then try again"));
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
            $this->generateAuthControllers($override);
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

    public function createRoleSeeder(bool $override = false): void
    {
        $seederPath = CubePath::make(config('cubeta-starter.seeder_path') . '/RoleSeeder.php');

        if ($seederPath->exist() and !$override) {
            CubeLog::add(new AlreadyExist($seederPath->fullPath, "Creating Role Seeder"));
            return;
        }

        $seederPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            [
                '{seederNamespace}' => config('cubeta-starter.seeder_namespace'),
                "{modelNamespace}"  => config('cubeta-starter.model_namespace'),
            ],
            $seederPath->fullPath,
            $override,
            CubePath::stubPath('RoleSeeder.stub')
        );
    }

    private function generateAuthControllers(bool $override = false): void
    {
        $apiControllerNamespace = config('cubeta-starter.api_controller_namespace');
        $apiServiceNamespace = config('cubeta-starter.service_namespace');

        $controllerPath = CubePath::make(config('cubeta-starter.api_controller_path') . "/$this->version/" . ucfirst(Str::studly($this->role)) . "AuthController.php");

        $stubProperties = [
            '{namespace}'        => "$apiControllerNamespace\\$this->version",
            '{serviceNamespace}' => "$apiServiceNamespace\\$this->version",
            '{role}'             => ucfirst(Str::studly($this->role)),
            '{roleEnumName}'     => $this->roleEnumNaming($this->role),
        ];

        if ($controllerPath->exist()) {
            CubeLog::add(new AlreadyExist($controllerPath->fullPath, "Generating Auth Controller For ({$this->role})"));
            return;
        }

        $controllerPath->ensureDirectoryExists();

        $this->generateFileFromStub(
            $stubProperties,
            $controllerPath->fullPath,
            $override,
            CubePath::stubPath('Auth/auth-controller.stub')
        );

        $apiRouteFile = CubePath::make("routes/{$this->version}/api/{$this->actorFileNaming($this->actor)}.php");

        if (!$apiRouteFile->exist()) {
            CubeLog::add(new CubeWarning("An Api File For ({$this->role}) Doesn't Exist\nRoutes For The Generated Controller Will Not Be Generated", "Generating Auth Controller For ({$this->role})"));
            return;
        }

        $publicAuthRoutesNames = $this->getAuthRouteNames(ContainerType::API, $this->role, true);
        $protectedAuthRoutesNames = $this->getAuthRouteNames(ContainerType::API, $this->role);

        $routes = FileUtils::generateStringFromStub(CubePath::stubPath('Auth/auth-api-routes.stub'), [
            "{{version}}"             => $this->version,
            "{{controllerName}}"      => ucfirst(Str::studly($this->role)),
            "{{role}}"                => $this->actorUrlName($this->role),
            "{{refresh-route}}"       => $protectedAuthRoutesNames['refresh'],
            "{{logout-route}}"        => $protectedAuthRoutesNames['logout'],
            "{{update-user-details}}" => $protectedAuthRoutesNames['update-user-details'],
            "{{user-details-route}}"  => $protectedAuthRoutesNames['user-details'],
        ]);

        $importStatement = "use " . config('cubeta-starter.api_controller_namespace') . "\\$this->version;";

        $apiRouteFile->putContent($routes, FILE_APPEND);
        FileUtils::addImportStatement($importStatement, $apiRouteFile);
        CubeLog::add(new ContentAppended($routes, $apiRouteFile->fullPath));

        $publicApiRouteFile = CubePath::make("/routes/{$this->version}/api/public.php");
        $publicAuthRoutes = FileUtils::generateStringFromStub(CubePath::stubPath('Auth/auth-public-api-routes.stub'), [
            "{{version}}"                      => $this->version,
            "{{controllerName}}"               => ucfirst(Str::studly($this->role)),
            "{{role}}"                         => $this->actorUrlName($this->role),
            "{{register-route}}"               => $publicAuthRoutesNames['register'],
            "{{login-route}}"                  => $publicAuthRoutesNames['login'],
            "{{password-reset-request}}"       => $publicAuthRoutesNames['password-reset-request'],
            "{{validate-password-reset-code}}" => $publicAuthRoutesNames['validate-reset-code'],
            "{{password-reset}}"               => $publicAuthRoutesNames['password-reset'],
        ]);

        if (!$publicApiRouteFile->exist()) {
            $this->addRouteFile(actor: 'public', version: $this->version);
        }

        $publicApiRouteFile->putContent($publicAuthRoutes, FILE_APPEND);
        FileUtils::addImportStatement($importStatement, $publicApiRouteFile);
        CubeLog::add(new ContentAppended($publicAuthRoutes, $publicApiRouteFile->fullPath));

        try {
            Postman::make()->getCollection()->newAuthApi($this->role)->save();
            CubeLog::add(new SuccessMessage("Postman Collection Now Has Folder For The Generated {$this->role} Auth Controller  \nRe-Import It In Postman"));
        } catch (Exception $e) {
            CubeLog::add($e);
        }
    }

    private function addPostmanAuthCollection(): void
    {
        $authPostmanEntity = file_get_contents(CubePath::stubPath('Auth/auth-postman-entity.stub'));
        $authPostmanEntity = str_replace("{role}", $this->role, $authPostmanEntity);
        $projectName = config('cubeta-starter.project_name');
        $collectionPath = CubePath::make(
            config('cubeta-starter.postman_collection _path') .
            $projectName .
            ".postman_collection.json"
        );

        if ($collectionPath->exist()) {
            $collection = $collectionPath->getContent();

            if (FileUtils::contentExistInFile($collectionPath, "\"name\":\"{$this->role} auth\",")) {
                CubeLog::add(new ContentAlreadyExist("Postman Collection For ({$this->role}) Auth Routes", "Adding Api Auth Routes To The Postman Collection"));
                return;
            }

            $collection = str_replace('"// add-your-cruds-here"', $authPostmanEntity, $collection);
            $collectionPath->putContent($collection);
        } else {
            $projectURL = config('cubeta-starter.project_url') ?? "http://localhost/" . $projectName . "/public/";
            $collectionStub = file_get_contents(CubePath::stubPath('postman-collection.stub'));
            $collectionStub = str_replace(
                ['{projectName}', '{project-url}', '// add-your-cruds-here'],
                [$projectName, $projectURL, $authPostmanEntity],
                $collectionStub
            );

            $collectionPath->ensureDirectoryExists();

            $collectionPath->putContent($collectionStub);
        }

        CubeLog::add(new ContentAppended("Postman Collection For ({$this->role}) Auth Routes", $collectionPath->fullPath));
    }
}
