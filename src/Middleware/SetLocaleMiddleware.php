<?php


namespace Cubeta\CubetaStarter\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the locale from the session
        $locale = Session::get('locale');

        // Set the locale for the current application
        if ($locale) {
            App::setLocale($locale);
        }

        // Continue the request
        return $next($request);
    }
}
