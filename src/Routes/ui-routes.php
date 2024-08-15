<?php

use Cubeta\CubetaStarter\App\Http\Controllers\GeneratorController;
use Cubeta\CubetaStarter\App\Http\Controllers\RenderAppropriateViewController;
use Illuminate\Support\Facades\Route;

Route::prefix('/cubeta-starter')->name('cubeta.starter.')->group(function () {
    Route::view('/settings', 'CubetaStarter::settings')->name('settings');
    Route::post('/settings', [GeneratorController::class, 'settingsHandler'])->name('settings.set');
    Route::post('add-actor', [GeneratorController::class, 'addActor'])->name('add.actor');
    Route::get('/clear-logs', [GeneratorController::class, 'clearLogs'])->name('clear.logs');
    Route::get('/', [GeneratorController::class, 'generatePage'])->name('generate.page');
    Route::post('/generate', [GeneratorController::class, 'generate'])->name('generate');
});
