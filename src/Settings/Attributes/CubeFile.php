<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Contracts\Tests\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString as TsxInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use JetBrains\PhpStorm\ExpectedValues;

class CubeFile extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty, HasModelCastColumn, HasPropertyValidationRule, HasTestAdditionalFactoryData, HasBladeInputComponent, HasDatatableColumnString, HasHtmlTableHeader, HasInterfacePropertyString, HasReactTsInputString, HasReactTsDisplayComponentString
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
            str("array{url:string,size:string,extension:string,mime_type:string}")
                ->when($this->nullable, fn($str) => $str->append("|null"))
                ->toString()
        );
    }

    public function modelCastColumn(): CastColumnString
    {
        return new CastColumnString(
            $this->name,
            "MediaCast::class . \":public,single\"",
            new PhpImportString("App\\Casts\\MediaCast")
        );
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        $rules = [
            ...$this->uniqueOrNullableValidationRules(),
            new ValidationRuleString(
                $this->isImageLike()
                    ? 'new MediaValidationRule'
                    : 'new MediaValidationRule(["file" , "max:10000"])',
                [
                    new PhpImportString('App\Rules\MediaValidationRule'),
                ]
            ),
        ];

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

    public function interfacePropertyString(): InterfacePropertyString
    {
        return new InterfacePropertyString(
            $this->name,
            "Media|undefined",
            true,
            new TsImportString("Media", "@/models/media")
        );
    }

    public function inputComponent(#[ExpectedValues(values: ['store', 'update'])] string $formType = "store", ?string $actor = null): TsxInputComponentString
    {
        return new TsxInputComponentString(
            "Input",
            $this->name,
            $this->titleNaming(),
            $this->isRequired,
            [
                [
                    'key' => 'onChange',
                    'value' => "(e) => setData(\"{$this->name}\", e.target.files?.[0])"
                ],
                [
                    'key' => 'type',
                    'value' => "'file'"
                ]
            ],
            [
                new TsImportString("Input", "@/components/form/fields/input")
            ]
        );
    }

    public function displayComponentString(): ReactTsDisplayComponentString
    {
        $modelVariable = $this->getOwnerTable()->variableNaming();
        $nullable = $this->nullable ? "?" : "";
        return new ReactTsDisplayComponentString(
            "DetailItem",
            $this->labelNaming(),
            "<Gallery sources={[{$modelVariable}{$nullable}.{$this->name}?.url]} />",
            [
                new TsImportString("Gallery", "@/components/ui/gallery"),
                new TsImportString("DetailItem", "@/components/ui/detail-item")
            ],
            [
                "className" => '"md:col-span-2"',
                'orientation' => '"vertical"',
            ]
        );
    }
}