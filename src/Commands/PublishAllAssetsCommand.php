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
            'cubeta-starter-api',
            'cubeta-starter-web',
        ];

        $output = "";

        foreach ($tags as $tag) {
            Artisan::call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $override
            ]);
            $output = $output . "\n" . Artisan::output();
            $this->info("$tag Has Been Published Successfully");
        }

        $this->addSetLocalRoute();

        $this->info($output);
    }
}
