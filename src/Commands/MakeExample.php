<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
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
                            'is_active' => ColumnTypeEnum::BOOLEAN->value
                        ],
                        'relations' => [
                            'products' => RelationsTypeEnum::HasMany->value,
                        ],
                        "container" => "both",
                        'actor' => 'none',
                        'nullables' => [
                            "description"
                        ],
                    ]);

                    $output = Artisan::output();

                    break;
                case "Product" :
                    Artisan::call('create:model', [
                        'name' => "Product",
                        "attributes" => [
                            "name" => "translatable",
                            "category_id" => "key",
                            "image" => "file",
                            'slug' => 'string',
                            'price' => ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                            'expire_at' => ColumnTypeEnum::DATETIME->value,
                            'manufacture_date' => ColumnTypeEnum::DATE->value,
                            'description' => ColumnTypeEnum::TRANSLATABLE->value,
                            'is_active' => ColumnTypeEnum::BOOLEAN->value
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
