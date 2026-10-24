<?php

namespace Cubeta\CubetaStarter\Enums;

enum RelationsTypeEnum: string
{
    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }
    case BelongsTo = 'belongsTo';
    case HasMany = 'hasMany';
    case HasOne = 'hasOne';
    case ManyToMany = 'manyToMany';
}
