<?php

use Cubeta\CubetaStarter\app\Http\Controllers\InitialProjectController;
use Illuminate\Support\Facades\Route;

Route::view('/cubeta-starter', 'CubetaStarter::greetings')->name('greetings');
Route::view('/cubeta-starter/initial', 'CubetaStarter::initial-project')->name('cubeta-starter.initial.page');
Route::post('/cubeta-starter/initial', [InitialProjectController::class, 'callInitialProject'])->name('call-initial-project');
