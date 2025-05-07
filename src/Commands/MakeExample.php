<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeExample extends Command
{
    protected $signature = "create:example";
    protected $description = "this command just to show the generated files for an already defined examples";

    public function handle(): void
    {
        $choice = $this->choice("Which One To Create", ["Category", "Product", "Brand"]);
        $output = null;
        $this->withProgressBar(1, function () use ($choice, &$output) {
            switch ($choice) {
                case "Category" :
                    Artisan::call('create:model', [
                        'name' => 'Category',
                        'attributes' => [
                            'name' => ColumnTypeEnum::TRANSLATABLE->value,
                            'title' => ColumnTypeEnum::TRANSLATABLE->value,
                            'description' => ColumnTypeEnum::TRANSLATABLE->value,
                            "image" => ColumnTypeEnum::FILE->value,
                            'is_active' => ColumnTypeEnum::BOOLEAN->value,
                            'slug' => ColumnTypeEnum::STRING->value,
                            'sort_order' => ColumnTypeEnum::INTEGER->value,
                            'total_products' => ColumnTypeEnum::BIG_INTEGER->value,
                            'total_views' => ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                            'average_product_rating' => ColumnTypeEnum::DOUBLE->value,
                            'commission_percentage' => ColumnTypeEnum::FLOAT->value,
                            'meta_description' => ColumnTypeEnum::TEXT->value,
                            'settings' => ColumnTypeEnum::JSON->value,
                            'launch_date' => ColumnTypeEnum::DATE->value,
                            'daily_update_time' => ColumnTypeEnum::TIME->value,
                            'last_promotion_date' => ColumnTypeEnum::DATETIME->value,
                            'last_accessed_at' => ColumnTypeEnum::TIMESTAMP->value,
                        ],
                        'relations' => [
                            'products' => RelationsTypeEnum::HasMany->value,
                        ],
                        "container" => "both",
                        'actor' => 'none',
                        'nullables' => [
                            "description",
                            "image"
                        ],
                        'uniques' => [
                            'slug',
                        ],
                    ]);

                    $output = Artisan::output();

                    break;
                case "Product" :
                    Artisan::call('create:model', [
                        'name' => "Product",
                        "attributes" => [
                            "name" => ColumnTypeEnum::TRANSLATABLE->value,
                            "category_id" => ColumnTypeEnum::KEY->value,
                            "image" => ColumnTypeEnum::FILE->value,
                            'slug' => ColumnTypeEnum::STRING->value,
                            'expire_at' => ColumnTypeEnum::DATETIME->value,
                            'manufacture_date' => ColumnTypeEnum::DATE->value,
                            'description' => ColumnTypeEnum::TRANSLATABLE->value,
                            'is_featured' => ColumnTypeEnum::BOOLEAN->value,
                            'stock_quantity' => ColumnTypeEnum::INTEGER->value,
                            'total_sales' => ColumnTypeEnum::BIG_INTEGER->value,
                            'views_count' => ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
                            'price' => ColumnTypeEnum::DOUBLE->value,
                            'weight_in_kg' => ColumnTypeEnum::FLOAT->value,
                            'sku' => ColumnTypeEnum::STRING->value,
                            'technical_specifications' => ColumnTypeEnum::TEXT->value,
                            'variants' => ColumnTypeEnum::JSON->value,
                            'restock_notification_time' => ColumnTypeEnum::TIME->value,
                            'last_purchased_at' => ColumnTypeEnum::TIMESTAMP->value,
                        ],
                        "relations" => [
                            "brands" => RelationsTypeEnum::ManyToMany->value
                        ],
                        "container" => ContainerType::BOTH,
                        'actor' => 'none',
                        'nullables' => [
                            "image"
                        ],
                        'uniques' => [
                            'slug',
                        ]
                    ]);
                    $output = Artisan::output();

                    break;
                case "Brand" :
                    Artisan::call("create:model", [
                        'name' => "Brand",
                        "attributes" => [
                            "name" => ColumnTypeEnum::TRANSLATABLE->value,
                            "description" => ColumnTypeEnum::TRANSLATABLE->value,

                            "products_count" => ColumnTypeEnum::INTEGER->value,
                            "total_revenue" => ColumnTypeEnum::BIG_INTEGER->value,
                            "market_rank" => ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,

                            "average_rating" => ColumnTypeEnum::DOUBLE->value,
                            "market_share" => ColumnTypeEnum::FLOAT->value,

                            "slug" => ColumnTypeEnum::STRING->value,
                            "brand_story" => ColumnTypeEnum::TEXT->value,
                            "social_links" => ColumnTypeEnum::JSON->value,

                            "is_premium" => ColumnTypeEnum::BOOLEAN->value,

                            "founded_date" => ColumnTypeEnum::DATE->value,
                            "support_hours" => ColumnTypeEnum::TIME->value,
                            "contract_expires" => ColumnTypeEnum::DATETIME->value,
                            "last_interaction" => ColumnTypeEnum::TIMESTAMP->value,

                            "logo" => ColumnTypeEnum::FILE->value,
                        ],
                        "relations" => [
                            "products" => RelationsTypeEnum::ManyToMany->value
                        ],
                        "container" => "both",
                        'actor' => 'none',
                        'nullables' => [
                            "logo",
                            "description",
                            "brand_story",
                            "social_links",
                            "parent_company_id",
                            "support_hours",
                            "contract_expires"
                        ],
                        'uniques' => [
                            'slug'
                        ]
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
