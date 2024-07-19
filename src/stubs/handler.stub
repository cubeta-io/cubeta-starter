<?php

namespace App\Exceptions;

use App\Http\Controllers\ApiController;
use App\Traits\RestTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler
{
    use RestTrait;

    public function __construct()
    {
    }

    public function handleException($request, Throwable $exception): Response|JsonResponse|RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($exception instanceof AuthenticationException) {
            return $this->apiResponse('', ApiController::STATUS_NOT_AUTHENTICATED, $exception->getMessage());
        }

        if ($exception instanceof AuthorizationException) {
            return $this->apiResponse('', ApiController::STATUS_UNAUTHORIZED, $exception->getMessage());
        }

        if ($exception instanceof HttpException) {
            if ($exception->getMessage() == 'Unauthorized Action') {
                return $this->apiResponse('', ApiController::STATUS_FORBIDDEN, $exception->getMessage());
            }

            return $this->apiResponse('', ApiController::STATUS_BAD_REQUEST, $exception->getMessage());
        }

        if ($exception instanceof HttpResponseException) {
            return $this->apiResponse('', ApiController::STATUS_FORBIDDEN, $exception->getMessage());
        }

        if ($exception instanceof ValidationException) {
            $msg = [
                'text'   => $exception->getMessage(),
                'errors' => $exception->errors(),
            ];

            return $this->apiResponse('', ApiController::STATUS_VALIDATION, $msg);
        }
        if ($exception instanceof ModelNotFoundException) {
            return $this->apiResponse('', ApiController::STATUS_NOT_FOUND, $exception->getMessage());
        }
        if ($exception instanceof RouteNotFoundException) {
            if ($exception->getMessage() == 'Route [login] not defined.') {
                return $this->apiResponse('', ApiController::STATUS_NOT_AUTHENTICATED, 'you should login');
            }
        }

        return $this->apiResponse('', ApiController::STATUS_NOT_FOUND, $exception->getMessage());
    }
}
