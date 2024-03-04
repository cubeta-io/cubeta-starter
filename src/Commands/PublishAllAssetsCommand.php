<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;

class PublishAllAssetsCommand extends Command
{
    use RouteBinding;

    protected $description = 'publish all package dependencies';

    protected $signature = 'cubeta-publish {--force}';

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
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
        $this->addRouteFile('public', ContainerType::API);
        $this->addRouteFile('protected', ContainerType::API);
        $this->addRouteFile('public', ContainerType::WEB);
        $this->addRouteFile('protected', ContainerType::WEB);

        $this->info($output);
    }
}
