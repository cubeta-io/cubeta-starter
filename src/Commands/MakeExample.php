<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeExample extends Command
{
    protected $signature = "create:example";
    protected $description = "this command just to show the generated files for an already defined examples";

    public function handle(): void
    {
        $choice = $this->choice("Which One To Create : ", ["Category", "Product", "Brand"]);
        $output = null;
        $this->withProgressBar(1, function () use ($choice, &$output) {
            switch ($choice) {
                case "Category" :
                    Artisan::call('create:model', [
                        'name' => 'Category',
                        'attributes' => [
                            'name' => 'translatable',
                            'title' => 'translatable',
                            'description' => 'text',
                            "image" => "file",
                        ],
                        'relations' => [
                            'products' => RelationsTypeEnum::HasMany->value,
                        ],
                        "container" => "both",
                        'actor' => 'none',
                        'nullables' => [
                            "description"
                        ],
                        'uniques' => [
                            'name'
                        ]
                    ]);

                    $output = Artisan::output();

                    break;
                case "Product" :
                    Artisan::call('create:model', [
                        'name' => "Product",
                        "attributes" => [
                            "name" => "translatable",
                            'title' => 'translatable',
                            "category_id" => "key",
                            "image" => "file",
                        ],
                        "relations" => [
                            "brands" => RelationsTypeEnum::ManyToMany->value
                        ],
                        "container" => "both",
                        'actor' => 'none',
                    ]);
                    $output = Artisan::output();

                    break;
                case "Brand" :
                    Artisan::call("create:model", [
                        'name' => "Brand",
                        "attributes" => [
                            "name" => "translatable",
                            'group' => 'translatable',
                            "image" => "file",
                        ],
                        "relations" => [
                            "products" => RelationsTypeEnum::ManyToMany->value
                        ],
                        "container" => "both",
                        'actor' => 'none',
                    ]);
                    $output = Artisan::output();

                    break;
                default:
                    $this->info("Undefined Value");
            }
        });

        $this->info("\n $output \n");
    }
}
