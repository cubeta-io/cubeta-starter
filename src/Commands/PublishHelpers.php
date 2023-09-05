<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PublishHelpers extends Command
{
    use AssistCommand;

    protected $description = 'Call publish helper command and perform the process of adding the helper files to the composer.json file';

    protected $signature = "publish-helpers";

    public function handle()
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'cubeta-starter-helpers',
            '--force' => true
        ]);

        $this->addAutoLoadsToComposerJson();

        $this->executeCommandInTheBaseDirectory('composer dump-autoload');
    }

    private function addAutoLoadsToComposerJson()
    {
        $composerPath = base_path('composer.json');

        if (file_exists($composerPath)) {
            $composerJsonContents = file_get_contents($composerPath);
            $composerJsonData = json_decode($composerJsonContents, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (!isset($composerJsonData['autoload'])) {
                    $composerJsonData['autoload'] = [];
                }

                if (!isset($composerJsonData['autoload']['files'])) {
                    $composerJsonData['autoload']['files'] = [];
                }

                $composerJsonData['autoload']['files'][] = "app/Helpers/NamingHelpers.php";
                $composerJsonData['autoload']['files'][] = "app/Helpers/TranslateHelpers.php";

                $updatedComposerJson = json_encode($composerJsonData, JSON_PRETTY_PRINT);

                if (file_put_contents($composerPath, $updatedComposerJson) !== false) {
                    echo "Autoload files added to composer.json successfully.";
                } else {
                    echo "Failed to write to composer.json.";
                }
            } else {
                echo "Error decoding composer.json as JSON.";
            }
        } else {
            echo "composer.json does not exist.";
        }
    }
}