<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PublishAllAssetsCommand extends Command
{
    protected $description = 'publish all package dependencies';

    protected $signature = 'cubeta-publish';

    public function handle(): void
    {
        $tags = [
            'cubeta-starter-repositories',
            'cubeta-starter-services',
            'cubeta-starter-api-controller',
            'cubeta-starter-middlewares',
            'cubeta-starter-helpers',
            'cubeta-starter-validation-rules',
            'cubeta-starter-traits',
            'cubeta-starter-config',
            'cubeta-starter-handler',
            'cubeta-starter-assets',
        ];

        $output = "";

        foreach ($tags as $tag) {
            if ($tag = 'cubeta-starter-handler') {
                Artisan::call('vendor:publish', [
                    '--tag' => 'cubeta-starter-handler',
                    '--force' => true
                ]);
            } else {
                Artisan::call('vendor:publish', [
                    '--tag' => $tag,
                ]);
            }
            $output = $output . "\n" . Artisan::output();
        }

        $this->info($output);
    }
}
