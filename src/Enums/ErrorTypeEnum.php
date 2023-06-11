<?php

namespace Cubeta\CubetaStarter\Enums;

class ErrorTypeEnum
{
    const INVALID_INPUT = 'Invalid input';

    const ALREADY_EXISTS = 'Already Exists';

    const FAILED_TO_CREATE_ROUTE_DIRECTORY = 'Failed To Create Your Route Specified Directory';

    const ACTOR_ROUTES_FILE_NOT_EXISTS = "Actor Routes Files Doesn't exist";

    const FAILED_TO_APPEND_ROUTE = 'Failed to Append a Route For This Controller';

    const ALL = [
        self::INVALID_INPUT,
        self::ACTOR_ROUTES_FILE_NOT_EXISTS,
        self::ALREADY_EXISTS,
        self::FAILED_TO_APPEND_ROUTE,
        self::FAILED_TO_CREATE_ROUTE_DIRECTORY
    ];
}
