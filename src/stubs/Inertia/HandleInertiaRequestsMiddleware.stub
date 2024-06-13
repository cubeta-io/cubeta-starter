<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'availableLocales' => config('cubeta-starter.available_locales'),
            'currentLocale'    => Session::get('locale') ?? "en",
            'authUser'         => auth()->user(),
            'currentRoute'     => Str::replace(env('APP_URL'), "", $request->fullUrl()),
            'asset'            => asset('/'),
            'baseUrl'          => (config('cubeta-starter.project_url') ?? config('app.url')) ?? '/',
            'csrfToken'        => csrf_token(),
            'message'          => session()->get('message') ?? null,
            'error'            => session()->get('error') ?? null,
            'success'          => session()->get('success') ?? null,
        ]);
    }
}
