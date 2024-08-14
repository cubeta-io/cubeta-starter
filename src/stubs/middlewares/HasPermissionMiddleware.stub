<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\RestTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Symfony\Component\HttpFoundation\Response;

class HasPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string  $permission
     * @param string  $model
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permission, string $model): Response
    {
        if ($request->expectsJson() && !auth('api')->user()?->hasPermission($permission, $model)) {
            return response()->json([
                                    'data'      => null,
                                    'status'    => false,
                                    'code'      => 403,
                                    'message'   => __('site.unauthorized_user')
                                ]);
        } elseif (!$request->expectsJson() && !auth('web')->user()?->hasPermission($permission, $model)) {
            abort(403, __('site.unauthorized_user'));
        }
        return $next($request);
    }
}
