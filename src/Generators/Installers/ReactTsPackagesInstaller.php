<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\Stub\Publisher;

class ReactTsPackagesInstaller extends AbstractGenerator
{
    public static string $key = "install-react-ts-packages";

    public string $type = 'installer';

    /**
     */
    public function run(): void
    {
        $this->preparePackageJson();

        PackageManager::composerInstall([
            "tightenco/ziggy",
            "maatwebsite/excel",
            "inertiajs/inertia-laravel"
        ]);

        FileUtils::executeCommandInTheBaseDirectory("php artisan ziggy:generate --types");

        //install js packages
        PackageManager::npmInstall([
            'laravel-vite-plugin',
            '@inertiajs/react',
            'tailwindcss',
            "@tailwindcss/vite",
            '@tailwindcss/forms',
            '@types/node',
            '@types/react',
            '@types/react-dom',
            '@vitejs/plugin-react',
            'react',
            'react-dom',
            'typescript',
            '@tinymce/tinymce-react',
            '@vitejs/plugin-react-refresh',
            'autoprefixer',
            'sweetalert2',
            'sweetalert2-react-content',
            'react-toastify',
            "vite"
        ]);

        $this->configurePrettier();
        Settings::make()->setInstalledWeb();
        Settings::make()->setFrontendType(FrontendTypeEnum::REACT_TS);
        Settings::make()->setInstalledWebPackages();
    }

    public function preparePackageJson(): void
    {
        $packageJsonPath = CubePath::make('/package.json');
        $jsonArray = json_decode($packageJsonPath->getContent(), true);

        if (isset($jsonArray['type']) && $jsonArray['type'] == "module") {
            return;
        } else {
            $jsonArray['type'] = "module";
            $packageJsonPath->putContent(json_encode($jsonArray, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));
            CubeLog::add(new ContentAppended('"type":"module"', $packageJsonPath->fullPath));
        }
    }

    /**
     * @return void
     */
    private function configurePrettier(): void
    {
        PackageManager::npmInstall([
            "prettier",
            "prettier-plugin-blade",
            "prettier-plugin-tailwindcss",
            "prettier-plugin-organize-imports"
        ], true);

        $prettierConfigPath = CubePath::make(".prettierrc");
        if ($prettierConfigPath->exist() && !$this->override) {
            $prettierConfigPath->logAlreadyExist("Installing web inertia react stack packages");
        } else {
            Publisher::make()
                ->source(CubePath::stubPath("Web/InertiaReact/Config/PrettierConfig.stub"))
                ->destination($prettierConfigPath)
                ->publish($this->override);
        }
    }
}
