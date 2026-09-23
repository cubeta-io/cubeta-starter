<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Modules\Postman;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\RoleFactoryMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Api\Controllers\RoleAuthControllerStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Seeders\RoleSeederStubBuilder;
use Cubeta\CubetaStarter\Stub\Publisher;
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

        $routeFile = $this->getRouteFilePath($this->generatedFor, $this->role, $this->version);
        if (ContainerType::isWeb($this->generatedFor)) {
            if (!$routeFile->exist()) {
                $this->addRouteFile($this->role, ContainerType::WEB, $this->version, [
                    'authenticated:web',
                    'role:' . $this->role,
                ]);
            }
        }

        if (ContainerType::isApi($this->generatedFor)) {
            if (!$routeFile->exist()) {
                $this->addRouteFile($this->role, ContainerType::API, $this->version, [
                    'authenticated:api',
                    'role:' . $this->role,
                    'jwt-auth',
                ]);
            }
        }

        $this->createRoleSeeder();

        $this->addRoleMethodToUserFactory();

        if ($this->authenticated && ContainerType::isApi($this->generatedFor)) {
            $this->generateAuthControllers();
            $this->generatePublicAuthRoutes();
            $this->generateProtectedAuthRoutes();
        }

        CubeLog::info("Don't forget to run [php artisan db:seed RoleSeeder]");
    }

    public function createRolesEnum(): void
    {
        $caseName = $this->roleEnumNaming($this->role);
        $caseValue = str($this->role)->lower()->singular();
        $placedPermissions = collect($this->permissions)
            ->map(fn($s) => "'" . str($s)->lower()->kebab()->toString() . "'")
            ->implode(",");

        $enumPath = CubePath::make("/app/Enums/RoleEnum.php");

        if (FileUtils::contentExistInFile($enumPath, "case $caseName")) {
            CubeLog::contentAlreadyExists("The Role ({$this->role})", $enumPath->fullPath, "Adding New Role Case To RoleEnum");
            return;
        }

        if (!$enumPath->exist()) {
            Publisher::make()
                ->source(CubePath::stubPath('Enums/RoleEnum.stub'))
                ->destination($enumPath)
                ->publish($this->override);
        }

        $content = $enumPath->getContent();

        $pattern = '#enum\s*RoleEnum\s*:\s*string\s*\{(.*)}#s';

        if (!preg_match($pattern, $content, $matches)) {
            CubeLog::failedAppending("case $caseName = '$caseValue';", $enumPath, "Adding new actor");
            return;
        }

        $content = str_replace($matches[1], "\n    case $caseName = '$caseValue';\n{$matches[1]}", $content);

        $pattern = '#match\s*\(\s*\$this\s*\)\s*\{(.*?)}#s';

        if (!preg_match($pattern, $content, $matches)) {
            CubeLog::failedAppending("self::$caseName => [$placedPermissions],", $enumPath, "Adding new actor");
        } else {
            $content = preg_replace_callback($pattern, function () use ($matches, $caseName, $placedPermissions) {
                return "match (\$this) {\n{$matches[1]}\n    self::$caseName => [$placedPermissions],\n}";
            }, $content, 1);
        }

        $enumPath->putContent($content);
        $enumPath->format();

        CubeLog::contentAppended("The Role ($this->role) Enum Case", $enumPath->fullPath);
    }

    /**
     * return the enum case name for a given role string, e.g. "super_admin" -> "SuperAdmin"
     * @param string $name
     * @return string
     */
    public function roleEnumNaming(string $name): string
    {
        return Str::studly(Str::singular($name));
    }

    public function createRoleSeeder(): void
    {
        $seederPath = CubePath::make(config('cubeta-starter.seeder_path') . '/RoleSeeder.php');

        RoleSeederStubBuilder::make()
            ->generate($seederPath, $this->override);

        ClassUtils::callInDatabaseSeeder(
            "RoleSeeder",
            "\\" . config('cubeta-starter.seeder_namespace') . "\RoleSeeder::class",
        );
    }

    private function generateAuthControllers(): void
    {
        $apiControllerNamespace = config('cubeta-starter.api_controller_namespace');
        $apiServiceNamespace = config('cubeta-starter.service_namespace');
        $controllerPath = CubePath::make(config('cubeta-starter.api_controller_path') . "/$this->version/" . ucfirst(Str::studly($this->role)) . "AuthController.php");

        RoleAuthControllerStubBuilder::make()
            ->namespace("$apiControllerNamespace\\$this->version")
            ->serviceNamespace("$apiServiceNamespace\\$this->version")
            ->role(str($this->role)->studly()->ucfirst())
            ->roleEnumName($this->roleEnumNaming($this->role))
            ->generate($controllerPath, $this->override);

        if (config('cubeta-starter.generate_postman_collection_for_api_routes')) {
            $this->addToPostman();
        }
    }

    private function generateProtectedAuthRoutes(): void
    {
        $apiRouteFile = $this->getRouteFilePath(ContainerType::API, $this->role, $this->version);
        $routes = Routes::apiProtectedAuthRoutes($this->role)
            ->map(fn(Routes $route) => $route->toString())
            ->implode("\n");

        $apiRouteFile->putContent($routes, FILE_APPEND);
        CubeLog::contentAppended($routes, $apiRouteFile->fullPath);

        $importStatement = new PhpImportString(config('cubeta-starter.api_controller_namespace') . "\\$this->version");
        FileUtils::addImportStatement($importStatement, $apiRouteFile);
    }

    private function generatePublicAuthRoutes(): void
    {
        $importStatement = new PhpImportString(config('cubeta-starter.api_controller_namespace') . "\\$this->version;");
        $publicApiRouteFile = $this->getRouteFilePath(ContainerType::API, "public", $this->version);

        $publicAuthRoutes = Routes::apiPublicAuthRoutes($this->role)
            ->map(fn(Routes $route) => $route->toString())
            ->implode("\n");

        if (!$publicApiRouteFile->exist()) {
            $this->addRouteFile(actor: 'public', version: $this->version);
        }

        $publicApiRouteFile->putContent($publicAuthRoutes, FILE_APPEND);
        CubeLog::contentAppended($publicAuthRoutes, $publicApiRouteFile->fullPath);

        FileUtils::addImportStatement($importStatement, $publicApiRouteFile);
    }

    /**
     * @return void
     */
    private function addToPostman(): void
    {
        try {
            Postman::make()->addAuthApi($this->role)->save();
            CubeLog::success("Postman Collection Now Has Folder For The Generated {$this->role} Auth Controller  \nRe-Import It In Postman");
        } catch (Exception $e) {
            CubeLog::add($e);
        }
    }

    private function addRoleMethodToUserFactory(): void
    {
        $method = new RoleFactoryMethodString($this->role);
        $userFactoryPath = CubePath::make(config('cubeta-starter.factory_path') . "/UserFactory.php");
        ClassUtils::addMethodToClass($userFactoryPath, $method->name, $method);
        foreach ($method->imports as $import) {
            FileUtils::addImportStatement($import, $userFactoryPath);
        }
    }
}
