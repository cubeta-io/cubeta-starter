<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\CastColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ValidationRuleString;

class CubeFile extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty, HasModelCastColumn, HasPropertyValidationRule, HasTestAdditionalFactoryData
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "UploadedFile::fake()->image(\"image.png\")",
            new ImportString("Illuminate\\Http\\UploadedFile")
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
            new ImportString("App\\Casts\\MediaCast")
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
                new ImportString("Illuminate\\Http\\UploadedFile"),
            ]
        );
    }
}