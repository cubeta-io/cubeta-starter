<?php

namespace Cubeta\CubetaStarter\Helpers;

use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;

class EnvParser
{
    private static $instance;
    public $environmentKeys = [];
    public CubePath $envPath;

    public function __construct()
    {
        $this->envPath = CubePath::make('/.env');
    }

    public static function make(): ?EnvParser
    {
        if (!app()->environment('local')) {
            return null;
        }

        if (!self::$instance) {
            self::$instance = new self();
        }

        self::$instance->environmentKeys = self::$instance->parseEnvFile();
        return self::$instance;
    }

    public function parseEnvFile(): array
    {
        if (!$this->envPath->exist()) {
            CubeLog::add(new NotFound($this->envPath->fullPath, "Parsing Your .env file"));
        }

        $envArray = [];
        $lines = file($this->envPath->fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            $value = trim($value, "\"'");

            $envArray[$name] = $value;
        }

        return $envArray;
    }

    public function hasValue(string $key): bool
    {
        return isset($this->environmentKeys[$key]) && $this->environmentKeys[$key] != "";
    }

    public function addVariable(string $key, string $value): void
    {
        if ($this->hasValue($key)) {
            CubeLog::add(new ContentAlreadyExist("$key", $this->envPath->fullPath, "Adding variable to the .env file"));
            return;
        }

        $content = $this->envPath->getContent();
        $content = $content . "\n\n$key=$value\n\n";
        $this->envPath->putContent($content);
        CubeLog::add(new ContentAppended("$key=$value", $this->envPath->fullPath));
    }
}
