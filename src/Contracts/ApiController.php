<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\RestTrait;
use Illuminate\Http\JsonResponse;

/**
 * Class ApiController
 */
class ApiController extends Controller
{
    use RestTrait;

    public array $relations = [];

    //Exception
    public const STATUS_BAD_REQUEST = 400;

    public const STATUS_CREATED = 201;

    public const STATUS_FORBIDDEN = 403;

    public const STATUS_NO_CONTENT = 204;

    public const STATUS_NOT_AUTHENTICATED = 402;

    public const STATUS_NOT_FOUND = 404;

    public const STATUS_OK = 200;

    public const STATUS_RESET_CONTENT = 205;

    public const STATUS_UNAUTHORIZED = 401;

    public const STATUS_VALIDATION = 405;

    public const TOKEN_EXPIRATION = 406;

    /**
     * @param mixed $response
     * @return JsonResponse
     */
    public function noData(mixed $response): JsonResponse
    {
        return $this->apiResponse($response, self::STATUS_OK, __('site.there_is_no_data'));
    }
}
