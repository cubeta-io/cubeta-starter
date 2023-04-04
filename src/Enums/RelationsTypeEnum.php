<?php

namespace Cubeta\CubetaStarter\Enums;

class RelationsTypeEnum
{
    const HasOne        =   'hasOne';

    const HasMany       =   'hasMany';

    const ManyToMany    =   'manyToMany';

    const BelongsTo     =   'belongsTo' ;

    const ALL = [
        self::HasOne ,
        self::HasMany ,
        self::BelongsTo ,
        self::ManyToMany
    ] ;
}
