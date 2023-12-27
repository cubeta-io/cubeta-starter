<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PublishAllAssetsCommand extends Command
{
    use RouteBinding, AssistCommand;

    protected $description = 'publish all package dependencies';

    protected $signature = 'cubeta-publish {--force}';

    /**
     * @return void
     */
    public function handle(): void
    {
        $override = $this->option('force') ?? false;

        $tags = [
            'cubeta-auth-views',
            'cubeta-starter-test-tools',
            'cubeta-starter-providers',
            'cubeta-starter-response',
            'cubeta-starter-crud',
            'cubeta-starter-locale',
            'cubeta-starter-assets',
            'cubeta-starter-config',
        ];

        $output = "";

        foreach ($tags as $tag) {
            Artisan::call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $override
            ]);
            $output = $output . "\n" . Artisan::output();
        }

        $this->addSetLocalRoute();

        Artisan::call('vendor:publish', [
            '--tag' => 'cubeta-starter-response',
            '--force' => true
        ]);
        $output . "\n" . Artisan::output();

        $this->info($output);
    }
}
