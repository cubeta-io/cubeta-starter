<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SetLocaleController extends Controller
{
    public function setLanguage(Request $request)
    {
        $lang = $request->lang;

        if (!in_array($lang, config('cubeta-starter.available_locales'))) {
            return response()->json(['message' => 'failed'] , 404);
        }

        session()->put('locale', $lang);

        // Set the locale for the current application
        app()->setLocale($lang);

        // Redirect back to the previous page
        return response()->json([
            'message' => 'success'
        ]);
    }
}
