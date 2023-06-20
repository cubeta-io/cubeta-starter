<?php

namespace Cubeta\CubetaStarter\Enums;

class RelationsTypeEnum
{
    public const ALL = [
        self::HasOne,
        self::HasMany,
        self::BelongsTo,
        self::ManyToMany,
    ];

    public const BelongsTo = 'belongsTo';

    public const HasMany = 'hasMany';
    public const HasOne = 'hasOne';

    public const ManyToMany = 'manyToMany';
}
