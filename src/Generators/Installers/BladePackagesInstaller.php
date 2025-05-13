<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\Settings;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;

class BladePackagesInstaller extends AbstractGenerator
{
    public static string $key = "install-blade-packages";

    public string $type = 'installer';

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function run(bool $override = false): void
    {
        PackageManager::composerInstall([
            "yajra/laravel-datatables",
            "maatwebsite/excel",
        ]);

        $this->configurePrettier();

        Artisan::call('vendor:publish', [
            '--tag' => 'datatables'
        ]);

        PackageManager::npmInstall([
            "vite",
            "laravel-vite-plugin",
            "jquery",
            "sass",
            "select2",
            "bootstrap",
            "bootstrap-icons",
            "laravel-vite-plugin",
            // UI tools
            "tinymce",
            // select 2
            "sweetalert2",
            "select2-bootstrap-5-theme",
            "baguettebox.js",
            // Datatables
            "datatables.net-bs5",
            "datatables.net-buttons-bs5",
            "datatables.net-fixedcolumns-bs5",
            "datatables.net-fixedheader-bs5",
            "datatables.net-select-bs5",
        ]);

        Settings::make()->setInstalledWeb();
        Settings::make()->setFrontendType(FrontendTypeEnum::BLADE);
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function configurePrettier(): void
    {
        PackageManager::npmInstall([
            "prettier",
            "prettier-plugin-blade"
        ], true);

        $prettierConfigPath = CubePath::make(".prettierrc");
        if ($prettierConfigPath->exist() && !$this->override) {
            $prettierConfigPath->logAlreadyExist("Installing web blade stack packages");
        } else {
            FileUtils::generateFileFromStub([], $prettierConfigPath->fullPath, CubePath::stubPath("Web/Blade/Config/PrettierConfig.stub"), $this->override);
            CubeLog::generatedSuccessfully($prettierConfigPath->fileName, $prettierConfigPath->fullPath, "Installing web blade stack packages");
            $prettierConfigPath->format();
        }
    }
}
