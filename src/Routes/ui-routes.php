<?php

use Cubeta\CubetaStarter\app\Http\Controllers\CreateModelController;
use Cubeta\CubetaStarter\app\Http\Controllers\InitialProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('/cubeta-starter')->name('cubeta-starter.')->group(function () {
    Route::view('/', 'CubetaStarter::greetings')->name('greetings');
    Route::view('/initial', 'CubetaStarter::initial-project')->name('initial.page');
    Route::post('/initial', [InitialProjectController::class, 'callInitialProject'])->name('call-initial-project');
    Route::get('/generate', [CreateModelController::class , 'createModelPage'])->name('generate.page');
    Route::post('/generate', [CreateModelController::class, 'callCreateModelCommand'])->name('call-create-model-command');
});
