<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Stub\Builders\Seeders\SeederStubBuilder;

class SeederGenerator extends AbstractGenerator
{
    public static string $key = 'seeder';

    public function run(bool $override = false): void
    {
        $seederPath = $this->table->getSeederPath();
        SeederStubBuilder::make()
            ->modelNamespace($this->table->getModelNamespace(false))
            ->modelName($this->table->modelNaming())
            ->generate($seederPath, $this->override);

        $this->callInDatabaseSeeder();
    }

    /**
     * Ensures that a specific seeder class call is added to the `DatabaseSeeder` file within the `run` method.
     * If it does not already exist, it appends the seeder class call to the list of existing seeders or creates the
     * seeder call if it's missing entirely.
     * @return void
     */
    private function callInDatabaseSeeder(): void
    {
        $dbSeederPath = CubePath::make('database/seeders/DatabaseSeeder.php');
        $seederName = $this->table->getSeederName();
        $seederCall = $this->table->getSeederClassString() . "::class";

        if (!$dbSeederPath->exist()) {
            return;
        }
        $seederContent = $dbSeederPath->getContent();
        $pattern = '/public\s*function\s*run\s*\(\s*\)\s*(?::\s*void)?\s*\{(.*?)\$this\s*->\s*call\(\s*\[(.*?)]\s*\);(.*?)}/s';
        if (preg_match($pattern, $seederContent, $matches)) {
            $exactMatch = $matches[2] ?? null;
            if (str($exactMatch)->contains($seederName)) {
                return;
            }

            if (!empty($exactMatch)) {
                if (str($exactMatch)->trim()->endsWith(',')) {
                    $replace = "$exactMatch\n$seederCall,\n";
                } else {
                    $replace = "$exactMatch,\n$seederCall,\n";
                }

                $seederContent = str_replace($exactMatch, $replace, $seederContent);
                $dbSeederPath->putContent($seederContent);
                CubeLog::contentAppended($seederCall, $dbSeederPath->fullPath);
                $dbSeederPath->format();
                return;
            }
        }

        $pattern = '/public\s*function\s*run\s*\(\s*\)\s*(?::\s*void)?\s*\{(.*?)}/s';
        if (preg_match($pattern, $seederContent, $matches)) {
            $exactMatch = $matches[1] ?? null;
            if (!empty($exactMatch)) {
                $newContent = "\$this->call([\n$seederCall,\n]);\n";
                $replace = "$exactMatch\n$newContent";
                $seederContent = str_replace($exactMatch, $replace, $seederContent);
                $dbSeederPath->putContent($seederContent);
                CubeLog::contentAppended($newContent, $dbSeederPath->fullPath);
                $dbSeederPath->format();
            }
        }
    }
}
