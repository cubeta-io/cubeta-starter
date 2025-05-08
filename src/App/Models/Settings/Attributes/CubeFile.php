<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Tests\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Javascript\DataTableColumnString;

class CubeFile extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty, HasModelCastColumn, HasPropertyValidationRule, HasTestAdditionalFactoryData, HasBladeInputComponent,HasDatatableColumnString,HasHtmlTableHeader
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "UploadedFile::fake()->image(\"image.png\")",
            new PhpImportString("Illuminate\\Http\\UploadedFile")
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "json",
            $this->nullable,
            $this->unique
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            $this->name,
            "array{url:string,size:string,extension:string,mime_type:string}"
        );
    }

    public function modelCastColumn(): CastColumnString
    {
        return new CastColumnString(
            $this->name,
            "MediaCast::class",
            new PhpImportString("App\\Casts\\MediaCast")
        );
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        $rules = [
            ...$this->uniqueOrNullableValidationRules(),
            new ValidationRuleString($this->isImageLike() ? 'image' : 'file'),
            new ValidationRuleString('max:10000'),
        ];

        if ($this->isImageLike()) {
            $rules[] = new ValidationRuleString('mimes:jpeg,png,jpg,gif,svg,webp');
        }

        return new PropertyValidationRuleString(
            $this->name,
            $rules
        );
    }

    private function isImageLike(): bool
    {
        return str($this->name)
            ->contains([
                'image',
                'profile',
                'icon',
                'img',
                'svg',
            ]);
    }

    public function testAdditionalFactoryData(): TestAdditionalFactoryDataString
    {
        return new TestAdditionalFactoryDataString(
            $this->name,
            'UploadedFile::fake()->image("image.png")',
            [
                new PhpImportString("Illuminate\\Http\\UploadedFile"),
            ]
        );
    }

    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString
    {
        return new InputComponentString(
            "file",
            "x-input",
            $this->name,
            $this->isRequired,
            $this->titleNaming(),
        );
    }

    public function bladeDisplayComponent(): DisplayComponentString
    {
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);
        $modelVariable = $table->variableNaming();
        return new DisplayComponentString(
            "x-image-preview",
            [
                [
                    "key" => ":imagePath",
                    "value" => "\${$modelVariable}->{$this->name}['url'] ?? ''"
                ]
            ]
        );
    }

    public function dataTableColumnString(): DataTableColumnString
    {
        return new DataTableColumnString(
            $this->name,
            <<<JS
                const filePath = data?.url;
                return `<div class="gallery">
                            <a href="\${filePath}">
                                <img class="img-fluid" style="max-width: 80px" src="\${filePath}" alt=""/>
                            </a>
                        </div>`
            JS,
            false,
            false,
        );
    }

    public function htmlTableHeader(): HtmlTableHeaderString

    {
        return new HtmlTableHeaderString(
            $this->labelNaming(),
        );
    }
}