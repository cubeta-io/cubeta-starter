<?php

namespace Cubeta\CubetaStarter\Enums;

final class ContainerType
{
    public const API = 'api';

    public const BOTH = 'both';

    public const WEB = 'web';

    public const ALL = [
        self::API,
        self::WEB,
        self::BOTH,
    ];
}
