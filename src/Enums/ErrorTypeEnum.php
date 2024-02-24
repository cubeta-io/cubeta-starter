<?php

namespace Cubeta\CubetaStarter\Enums;

class ErrorTypeEnum
{
    public const ALL_ERRORS = [
        self::INVALID_INPUT,
        self::ACTOR_ROUTES_FILE_NOT_EXISTS,
        self::ALREADY_EXISTS,
        self::FAILED_TO_APPEND_ROUTE,
        self::FAILED_TO_CREATE_ROUTE_DIRECTORY,
        self::CLASS_EXISTS,
    ];
    public const ACTOR_ROUTES_FILE_NOT_EXISTS = "Actor Routes Files Does not exist";

    public const ALREADY_EXISTS = 'Already Exists';

    public const FAILED_TO_APPEND_ROUTE = 'Failed to Append a Route For This Controller';

    public const FAILED_TO_CREATE_ROUTE_DIRECTORY = 'Failed To Create Your Route Specified Directory';

    public const INVALID_INPUT = 'Invalid input';

    public const CLASS_EXISTS = 'The class exists!';
}
