<?php

namespace App\Exceptions;

use Cubeta\CubetaStarter\Contracts\ApiController;
use Cubeta\CubetaStarter\Traits\RestTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    use RestTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    public function render($request, Throwable $exception): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if (!$request->acceptsHtml()) {
            return $this->handleException($request, $exception);
        } else {
            return parent::render($request , $exception);
        }
    }

    /**
     * @throws Throwable
     */
    public function handleException($request, Throwable $exception): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($exception instanceof AuthenticationException) {
            return $this->apiResponse('', ApiController::STATUS_NOT_AUTHENTICATED, $exception->getMessage());
        }

        if ($exception instanceof AuthorizationException) {
            return $this->apiResponse('', ApiController::STATUS_UNAUTHORIZED, $exception->getMessage());
        }

        if ($exception instanceof UnauthorizedException) {
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
                'text' => $exception->getMessage(),
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
        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        return $this->apiResponse('', ApiController::STATUS_NOT_FOUND, $exception->getMessage());
    }
}
