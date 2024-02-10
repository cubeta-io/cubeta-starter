<?php

namespace Cubeta\CubetaStarter\Enums;

enum RelationsTypeEnum: string
{
    case BelongsTo = 'belongsTo';
    case HasMany = 'hasMany';
    case HasOne = 'hasOne';
    case ManyToMany = 'manyToMany';

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
