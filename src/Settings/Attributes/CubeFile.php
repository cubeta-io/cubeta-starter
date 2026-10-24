<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use JetBrains\PhpStorm\ExpectedValues;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Dtos\DtoPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Contracts\Tests\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString as TsxInputComponentString;

class CubeFile extends CubeAttribute implements HasBladeInputComponent, HasDataTableColumnObjectString, HasDatatableColumnString, HasDocBlockProperty, HasFakeMethod, HasHtmlTableHeader, HasInterfacePropertyString, HasMigrationColumn, HasModelCastColumn, HasPropertyValidationRule, HasReactTsDisplayComponentString, HasReactTsInputString, HasTestAdditionalFactoryData
{
    private function isImageLike(): bool
    {
        return str($this->name)
            ->contains([
                'image',
                'photo',
                'picture',
                'profile',
                'avatar',
                'logo',
                'icon',
                'thumbnail',
                'thumb',
                'banner',
                'cover',
                'poster',
                'img',
                'svg',
            ]);
    }

    public function bladeDisplayComponent(): DisplayComponentString
    {
        $table = $this->table() ?? CubeTable::create($this->parentTableName);
        $modelVariable = $table->variableNaming();

        return new DisplayComponentString(
            'x-image-preview',
            [
                [
                    'key'   => ':imagePath',
                    'value' => "\${$modelVariable}->{$this->name}['url'] ?? ''",
                ],
            ]
        );
    }

    public function bladeInputComponent(string $formType = 'store', ?string $actor = null): InputComponentString
    {
        return new InputComponentString(
            'file',
            'x-input',
            $this->name,
            $this->isRequired,
            $this->titleNaming(),
        );
    }

    public function datatableColumnObject(?string $actor = null): DataTableColumnObjectString
    {
        if (! $this->isImageLike()) {
            return new DataTableColumnObjectString(
                $this->name,
                $this->labelNaming(),
                false,
                false,
                'return cell?.url ? (<a href={cell.url} download><Button size={"icon"}><DownloadIcon/></Button></a>) : undefined;',
                [
                    new TsImportString('Button', '@/components/ui/button', false),
                    new TsImportString('DownloadIcon', 'lucide-react', false),
                ],
            );
        }

        return new DataTableColumnObjectString(
            $this->name,
            $this->labelNaming(),
            false,
            false,
            'return (<ImagePreview src={cell?.url} />);',
            [
                new TsImportString('ImagePreview', '@/components/ui/image-preview'),
            ],
        );
    }

    public function dataTableColumnString(): DataTableColumnString
    {
        return new DataTableColumnString(
            $this->name,
            <<<'JS'
                const filePath = data?.url;
                return `<div class="gallery">
                            <a href="${filePath}">
                                <img class="img-fluid" style="max-width: 80px" src="${filePath}" alt=""/>
                            </a>
                        </div>`;
            JS,
            false,
            false,
        );
    }

    public function displayComponentString(): ReactTsDisplayComponentString
    {
        $modelVariable = $this->table()->variableNaming();
        $nullable = $this->nullable ? '?' : '';

        if (! $this->isImageLike()) {
            $fileUrl = "{$modelVariable}{$nullable}.{$this->name}?.url";

            return new ReactTsDisplayComponentString(
                'DetailItem',
                $this->labelNaming(),
                $fileUrl.' ? (<a href={'.$fileUrl.'} download><Button size="icon"><DownloadIcon/></Button></a>) : undefined',
                [
                    new TsImportString('Button', '@/components/ui/button', false),
                    new TsImportString('DownloadIcon', 'lucide-react', false),
                    new TsImportString('DetailItem', '@/components/ui/detail-item'),
                ],
            );
        }

        return new ReactTsDisplayComponentString(
            'DetailItem',
            $this->labelNaming(),
            "<Gallery sources={[{$modelVariable}{$nullable}.{$this->name}?.url]} />",
            [
                new TsImportString('Gallery', '@/components/ui/gallery'),
                new TsImportString('DetailItem', '@/components/ui/detail-item'),
            ],
            [
                'className'   => '"md:col-span-2"',
                'orientation' => '"vertical"',
            ]
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            $this->name,
            str('SerializedMedia')
                ->when($this->nullable, fn ($str) => $str->append('|null'))
                ->toString(),
            imports: [
                new PhpImportString("App\Serializers\SerializedMedia"),
            ]
        );
    }

    public function dtoProperty(): DtoPropertyString
    {
        return new DtoPropertyString(
            $this->name,
            'UploadedFile',
            $this->nullable,
            [new PhpImportString('Illuminate\\Http\\UploadedFile')]
        );
    }

    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            'UploadedFile::fake()->image("image.png")',
            new PhpImportString('Illuminate\\Http\\UploadedFile')
        );
    }

    public function htmlTableHeader(): HtmlTableHeaderString
    {
        return new HtmlTableHeaderString(
            $this->labelNaming(),
        );
    }

    public function inputComponent(#[ExpectedValues(values: ['store', 'update'])] string $formType = 'store', ?string $actor = null): TsxInputComponentString
    {
        $props = [
            [
                'key'   => 'onChange',
                'value' => "(file) => setData(\"{$this->name}\", file)",
            ],
            [
                'key'   => 'acceptedFileTypes',
                'value' => "['image/*']",
            ],
        ];

        if ($formType == 'update') {
            $props[] = [
                'key'   => 'defaultValue',
                'value' => "{$this->table()->variableNaming()}.{$this->name}",
            ];
        }

        return new TsxInputComponentString(
            'FilepondInput',
            $this->name,
            $this->titleNaming(),
            $this->isRequired,
            $props,
            [
                new TsImportString('FilepondInput', '@/components/form/fields/filepond/filepond-input'),
            ]
        );
    }

    public function interfacePropertyString(): InterfacePropertyString
    {
        return new InterfacePropertyString(
            $this->name,
            'File|Media|null|undefined',
            true,
            new TsImportString('Media', '@/models/media')
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            'json',
            $this->nullable,
            $this->unique
        );
    }

    public function modelCastColumn(): CastColumnString
    {
        return new CastColumnString(
            $this->name,
            'MediaCast::single(private: false)',
            new PhpImportString('App\\Casts\\MediaCast')
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

    public function testAdditionalFactoryData(): TestAdditionalFactoryDataString
    {
        return new TestAdditionalFactoryDataString(
            $this->name,
            'UploadedFile::fake()->image("image.png")',
            [
                new PhpImportString('Illuminate\\Http\\UploadedFile'),
            ]
        );
    }
}
