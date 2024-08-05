<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\AlreadyExist;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class TestGenerator extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "test";

    public function run(bool $override = false): void
    {
        $baseRouteName = $this->getRouteName($this->table, ContainerType::API, $this->actor) . '.';

        $testPath = $this->table->getTestPath();

        if ($testPath->exist()) {
            CubeLog::add(new AlreadyExist($testPath->fullPath, "Generating Test For ({$this->table->modelName}) Model"));
            return;
        }

        $testPath->ensureDirectoryExists();

        $stubProperties = [
            '{namespace}'             => config('cubeta-starter.test_namespace'),
            '{modelName}'             => $this->table->modelName,
            '{{actor}}'               => $this->actor,
            '{baseRouteName}'         => $baseRouteName,
            '{modelNamespace}'        => config('cubeta-starter.model_namespace'),
            '{resourceNamespace}'     => $this->table->getResourceNameSpace(false),
            '{additionalFactoryData}' => $this->getAdditionalFactoryData(),
        ];

        $this->generateFileFromStub(
            $stubProperties,
            $testPath->fullPath,
        );

        $testPath->format();
    }

    private function getAdditionalFactoryData(): string
    {
        $data = '';
        $this->table->attributes()->each(function (CubeAttribute $att) use (&$data) {
            if ($att->isFile()) {
                $data .= "'{$att->name}' => \Illuminate\Http\UploadedFile::fake()->image('image.jpg'),\n";
            } elseif ($att->isDateTime()) {
                $data .= "'{$att->name}' => now()->format('Y-m-d H:i:s'), \n";
            }
        });

        return $data;
    }

    protected function stubsPath(): string
    {
        return CubePath::stubPath('test.stub');
    }
}
