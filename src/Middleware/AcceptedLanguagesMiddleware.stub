<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AcceptedLanguagesMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {

        if ($request->acceptsHtml()) {
            // Get the locale from the session
            $locale = Session::get('locale');
        } else {
            $locale = $request->header('Accept-Language', 'en');
        }

        // if not exists in the project locales set the app locale to the default app locale
        if (!in_array($locale, config('cubeta-starter.available_locales'))) {
            App::setLocale(config('cubeta-starter.default_locale'));
        }

        // Set the locale for the current application
        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
