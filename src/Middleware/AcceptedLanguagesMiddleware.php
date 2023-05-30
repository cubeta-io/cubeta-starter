<?php

namespace Cubeta\CubetaStarter\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AcceptedLanguagesMiddleware
{
    private Application $app;

    private array $languages;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->languages = config('cubeta-starter.available_locales');
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $this->app->setLocale($this->parseHttpLocale($request));
        Carbon::setLocale($this->parseHttpLocale($request));

        return $next($request);
    }

    private function parseHttpLocale(Request $request): string
    {
        $list = explode(',', $request->server('HTTP_ACCEPT_LANGUAGE'));

        $locales = Collection::make($list)->map(function ($locale) {
            $parts = explode(';', $locale);
            $mapping['locale'] = trim($parts[0]);
            if (isset($parts[1])) {
                $factorParts = explode('=', $parts[1]);
                $mapping['factor'] = $factorParts[1];
            } else {
                $mapping['factor'] = 1;
            }
            if (! in_array($mapping['locale'], $this->languages)) {
                $mapping['locale'] = $this->languages[0];
            }

            return $mapping;
        })->sortByDesc(function ($locale) {
            return $locale['factor'];
        });

        return $locales->first()['locale'];
    }
}
