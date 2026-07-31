<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\PackageManager;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\Stub\Publisher;
use Illuminate\Support\Facades\Artisan;

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
            'react',
            'react-dom',
            '@types/react',
            '@types/react-dom',
            '@inertiajs/react',
            "@inertiajs/vite",
            '@vitejs/plugin-react',
            'tailwindcss',
            "@tailwindcss/vite",
            'typescript',
            "vite",
            'laravel-vite-plugin',
            '@types/node',
            //tiptap packages
            "@tiptap/extension-blockquote",
            "@tiptap/extension-bullet-list",
            "@tiptap/extension-character-count",
            "@tiptap/extension-color",
            "@tiptap/extension-font-family",
            "@tiptap/extension-gapcursor",
            "@tiptap/extension-heading",
            "@tiptap/extension-horizontal-rule",
            "@tiptap/extension-image",
            "@tiptap/extension-link",
            "@tiptap/extension-list-item",
            "@tiptap/extension-ordered-list",
            "@tiptap/extension-table",
            "@tiptap/extension-table-cell",
            "@tiptap/extension-table-header",
            "@tiptap/extension-table-row",
            "@tiptap/extension-text-align",
            "@tiptap/extension-text-style",
            "@tiptap/extension-youtube",
            "@tiptap/react",
            "@tiptap/starter-kit",
            // filepond
            "filepond",
            "filepond-plugin-file-poster",
            "filepond-plugin-file-validate-type",
            "filepond-plugin-image-exif-orientation",
            "filepond-plugin-image-preview",
            "react-filepond",
        ]);

        $this->configurePrettier();
        Settings::make()->setInstalledWeb();
        Settings::make()->setFrontendType(FrontendTypeEnum::REACT_TS);
        Settings::make()->setInstalledWebPackages();

        Artisan::call('vendor:publish', [
            '--provider' => 'Inertia\\ServiceProvider',
            '--force' => true
        ]);

        Artisan::call('view:clear');
    }

    public function preparePackageJson(): void
    {
        $packageJsonPath = CubePath::make('/package.json');

        if (!$packageJsonPath->exist()) {
            FileUtils::executeCommandInTheBaseDirectory("npm init -y");
        }

        $jsonArray = json_decode($packageJsonPath->getContent(), true);

        if (isset($jsonArray['type']) && $jsonArray['type'] == "module") {
            return;
        } else {
            $jsonArray['type'] = "module";
            $packageJsonPath->putContent(json_encode($jsonArray, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));
            CubeLog::contentAppended('"type":"module"', $packageJsonPath->fullPath);
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
