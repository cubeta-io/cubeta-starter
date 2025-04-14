<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\Logs\CubeInfo;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Illuminate\Support\Arr;

class PackageManager
{
    public static function composerPackageInstalled(string $packageName): bool
    {
        $packageJson = self::packageJson();

        if (!isset($packageJson['require'][$packageName])
            && !isset($packageJson['require-dev'][$packageName])) {
            return false;
        }

        $output = shell_exec("composer show $packageName 2>&1");
        if (str_contains($output, 'not found') || !str_contains($output, $packageName)) {
            return false;
        }

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (stripos($line, 'versions') === 0) {
                $version = trim(str_replace('versions :', '', $line));
                CubeLog::add(new CubeInfo("$packageName is installed with version $version."));
                break;
            }
        }

        return true;
    }

    public static function composerInstall(string|array $packages): void
    {
        $packages = Arr::wrap($packages);

        $required = [];
        foreach ($packages as $package) {
            if (!self::composerPackageInstalled($package)) {
                $required[] = $package;
            }
        }

        if (count($required) > 0) {
            $command = "composer require " . implode(" ", $required);
            FileUtils::executeCommandInTheBaseDirectory($command);
        }
    }

    public static function npmPackageInstalled($packageName): bool
    {
        $packageJson = self::packageJson();

        if (!isset($packageJson['devDependencies'][$packageName])
            && !isset($packageJson['dependencies'][$packageName])) {
            return false;
        }

        $output = FileUtils::executeCommandInTheBaseDirectory("npm list $packageName --depth=0 2>&1");

        if (str_contains($output, 'ERR!') || !str_contains($output, $packageName)) {
            return false;
        }

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (str_contains($line, $packageName . '@')) {
                $parts = explode('@', $line);
                $version = trim(end($parts));
                CubeLog::add(new CubeInfo("$packageName is installed with version $version."));
                break;
            }
        }

        return true;
    }

    public static function npmInstall(string|array $packages, bool $isDev = false): void
    {
        $packages = Arr::wrap($packages);

        $required = [];
        foreach ($packages as $package) {
            if (!self::npmPackageInstalled($package)) {
                $required[] = $package;
            }
        }

        if (count($required) > 0) {
            $command = "npm install " . implode(" ", $required);
            if ($isDev) {
                $command .= " --save-dev --save-exact";
            }

            FileUtils::executeCommandInTheBaseDirectory($command);
        }
    }

    public static function packageJson()
    {
        $packageJson = CubePath::make('package.json');

        if (!$packageJson->exist()) {
            return null;
        }

        return json_decode($packageJson->getContent(), true);
    }

    public static function composerJson()
    {
        $composerJson = CubePath::make('composer.json');
        if (!$composerJson->exist()) {
            return null;
        }
        return json_decode($composerJson->getContent(), true);
    }
}