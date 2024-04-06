<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;

class WebPackagesInstallers extends AbstractGenerator
{
    public static string $key = "install-web-packages";

    public string $type = 'installer';

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/package.json.stub';
    }

    public function run(bool $override = false): void
    {
        $pkgJson = CubePath::make("/package.json");

        if ($pkgJson->exist()) {
            $jsonContent = $pkgJson->getContent();
            $jsonContent = json_decode($jsonContent, true);

            if (isset($jsonContent['devDependencies'])) {
                $jsonContent['devDependencies'] = [
                    "baguettebox.js" => "^1.11.1",
                    "bootstrap" => "^5.2.3",
                    "datatables.net-fixedcolumns-bs5" => "^4.3.0",
                    "datatables.net-fixedheader-bs5" => "^3.4.0",
                    "jquery" => "^3.7.1",
                    "laravel-datatables-vite" => "^0.5.2",
                    "sass" => "^1.72.0",
                    "select2" => "^4.1.0-rc.0",
                    "select2-bootstrap-5-theme" => "^1.3.0",
                    "sweetalert2" => "^11.6.13",
                    "tinymce" => "^6.7.3",
                    ...$jsonContent['devDependencies']
                ];
            } else {
                $jsonContent['devDependencies'] = [
                    "baguettebox.js" => "^1.11.1",
                    "bootstrap" => "^5.2.3",
                    "datatables.net-fixedcolumns-bs5" => "^4.3.0",
                    "datatables.net-fixedheader-bs5" => "^3.4.0",
                    "jquery" => "^3.7.1",
                    "laravel-datatables-vite" => "^0.5.2",
                    "sass" => "^1.72.0",
                    "select2" => "^4.1.0-rc.0",
                    "select2-bootstrap-5-theme" => "^1.3.0",
                    "sweetalert2" => "^11.6.13",
                    "tinymce" => "^6.7.3",
                ];
            }

            $pkgJson->putContent(json_encode($jsonContent, JSON_PRETTY_PRINT));
        } else {

            $this->generateFileFromStub([], $pkgJson->fullPath);
        }

        FileUtils::executeCommandInTheBaseDirectory('composer require yajra/laravel-datatables');

        FileUtils::executeCommandInTheBaseDirectory("npm install");
    }
}
