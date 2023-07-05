<?php

use Illuminate\Support\Facades\Route;
use Cubeta\CubetaStarter\app\Http\Controllers\CallAppropriateCommand;
use Cubeta\CubetaStarter\app\Http\Controllers\RenderAppropriateViewController;

Route::prefix('/cubeta-starter')->name('cubeta-starter.')->group(function () {
    Route::view('/', 'CubetaStarter::complete-install')->name('complete-installation');
    Route::view('/output', 'CubetaStarter::command-output')->name('output');
    Route::prefix('generate')->name('generate-')->group(function () {
        Route::get('/', [RenderAppropriateViewController::class, 'fullGenerate'])->name('full.page');
        Route::get('/migration', [RenderAppropriateViewController::class, 'generateMigration'])->name('migration.page');
        Route::get('/factory', [RenderAppropriateViewController::class, 'generateFactory'])->name('factory.page');
        Route::get('/seeder', [RenderAppropriateViewController::class, 'generateSeeder'])->name('seeder.page');
        Route::get('/repository', [RenderAppropriateViewController::class, 'generateRepository'])->name('repository.page');
        Route::get('/service', [RenderAppropriateViewController::class, 'generateService'])->name('service.page');
        Route::get('/request', [RenderAppropriateViewController::class, 'generateRequest'])->name('request.page');
        Route::get('/resource', [RenderAppropriateViewController::class, 'generateResource'])->name('resource.page');
        Route::get('/api-controller', [RenderAppropriateViewController::class, 'generateController'])->name('api-controller.page');
        Route::get('/test', [RenderAppropriateViewController::class, 'generateTest'])->name('test.page');
        Route::get('/policy', [RenderAppropriateViewController::class, 'generatePolicy'])->name('policy.page');
        Route::get('/postman-collection', [RenderAppropriateViewController::class, 'generatePostmanCollection'])->name('postman-collection.page');
        Route::get('/add-actor', [RenderAppropriateViewController::class, 'addActor'])->name('add-actor.page');
        Route::get('/web-controller', [RenderAppropriateViewController::class, 'generateWebController'])->name('web-controller.page');
    });
    Route::post('/full-generate', [CallAppropriateCommand::class, 'callCreateModelCommand'])->name('call-create-model-command');
    Route::post('/migration', [CallAppropriateCommand::class, 'callCreateMigrationCommand'])->name('call-create-migration-command');
    Route::post('/factory', [CallAppropriateCommand::class, 'callCreateFactoryCommand'])->name('call-create-factory-command');
    Route::post('/seeder', [CallAppropriateCommand::class, 'callCreateSeederCommand'])->name('call-create-seeder-command');
    Route::post('/repository', [CallAppropriateCommand::class, 'callCreateRepositoryCommand'])->name('call-create-repository-command');
    Route::post('/service', [CallAppropriateCommand::class, 'callCreateServiceCommand'])->name('call-create-service-command');
    Route::post('/request', [CallAppropriateCommand::class, 'callCreateRequestCommand'])->name('call-create-request-command');
    Route::post('/resource', [CallAppropriateCommand::class, 'callCreateResourceCommand'])->name('call-create-resource-command');
    Route::post('/api-controller', [CallAppropriateCommand::class, 'callCreateApiControllerCommand'])->name('call-create-api-controller-command');
    Route::post('/test', [CallAppropriateCommand::class, 'callCreateTestCommand'])->name('call-create-test-command');
    Route::post('/policy', [CallAppropriateCommand::class, 'callCreatePolicyCommand'])->name('call-create-policy-command');
    Route::post('/postman-collection', [CallAppropriateCommand::class, 'callCreatePostmanCollectionCommand'])->name('call-create-postman-collection-command');
    Route::post('/web-controller', [CallAppropriateCommand::class, 'callCreateWebControllerCommand'])->name('call-create-web-controller-command');
    Route::post('/add-actor', [CallAppropriateCommand::class, 'callAddActorCommand'])->name('call-add-actor-command');
    Route::get('/install-spatie', [CallAppropriateCommand::class, 'callInstallSpatie'])->name('call-install-spatie');
    Route::get('/publish-config', [CallAppropriateCommand::class, 'publishConfig'])->name('config-publish');
    Route::get('/publish-handler', [CallAppropriateCommand::class, 'publishHandler'])->name('publish-handler');
    Route::get('publish-assets', [CallAppropriateCommand::class, 'publishAssets'])->name('publish-assets');
    Route::get('/install-web-packages', [CallAppropriateCommand::class, 'installingWebPackages'])->name('install-web-packages');
});
