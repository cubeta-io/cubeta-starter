<?php

namespace cubeta\CubetaStarter\Contracts;

use App\Http\Controllers\Controller;
use cubeta\CubetaStarter\Traits\RestfulTrait;

/**
 * Class ApiController
 */
class ApiController extends Controller
{
    use RestfulTrait;

    const STATUS_OK = 200;

    const STATUS_CREATED = 201;

    const STATUS_NO_CONTENT = 204;

    const STATUS_RESET_CONTENT = 205;

    //Exception
    const STATUS_BAD_REQUEST = 400;

    const STATUS_UNAUTHORIZED = 401;

    const STATUS_NOT_AUTHENTICATED = 402;

    const STATUS_FORBIDDEN = 403;

    const STATUS_NOT_FOUND = 404;

    const STATUS_VALIDATION = 405;

    const TOKEN_EXPIRATION = 406;
}
