<?php

use Illuminate\Support\Facades\Route;
use Cubeta\CubetaStarter\App\Http\Controllers\CallAppropriateCommand;
use Cubeta\CubetaStarter\App\Http\Controllers\RenderAppropriateViewController;

Route::prefix('/cubeta-starter')->name('cubeta-starter.')->group(function () {
    Route::view('/', 'CubetaStarter::complete-install')->name('complete-installation');
    Route::view('/output', 'CubetaStarter::command-output')->name('output');

    Route::controller(RenderAppropriateViewController::class)->group(function () {
        Route::get('/docs', 'getDocumentation')->name('get-documentation');

        Route::prefix('generate')->name('generate-')->group(function () {
            Route::get('/', 'fullGenerate')->name('full.page');
            Route::get('/model' , 'generateModel')->name('generate-model');
            Route::get('/migration', 'generateMigration')->name('migration.page');
            Route::get('/factory', 'generateFactory')->name('factory.page');
            Route::get('/seeder', 'generateSeeder')->name('seeder.page');
            Route::get('/repository', 'generateRepository')->name('repository.page');
            Route::get('/service', 'generateService')->name('service.page');
            Route::get('/request', 'generateRequest')->name('request.page');
            Route::get('/resource', 'generateResource')->name('resource.page');
            Route::get('/api-controller', 'generateController')->name('api-controller.page');
            Route::get('/test', 'generateTest')->name('test.page');
            Route::get('/postman-collection', 'generatePostmanCollection')->name('postman-collection.page');
            Route::get('/add-actor', 'addActor')->name('add-actor.page');
            Route::get('/web-controller', 'generateWebController')->name('web-controller.page');
        });
    });

    Route::controller(CallAppropriateCommand::class)->group(function () {

        Route::prefix('/publishes')->group(function () {
            Route::get('/publish-web', 'installWeb')->name('web.publish');
            Route::get('/publish-api', 'installApi')->name('api.publish');
            Route::get('/all', 'installAll')->name('publish-all');
        });

        Route::prefix('make')->name('call-')->group(function () {
            Route::post('/full-generate', 'fullGeneration')->name('full-generate');
            Route::post('/migration', 'generateMigration')->name('create-migration-command');
            Route::post('/factory', 'generateFactory')->name('create-factory-command');
            Route::post('/seeder', 'generateSeeder')->name('create-seeder-command');
            Route::post('/repository', 'generateRepository')->name('create-repository-command');
            Route::post('/service', 'generateService')->name('create-service-command');
            Route::post('/request', 'generateRequest')->name('create-request-command');
            Route::post('/resource', 'generateResource')->name('create-resource-command');
            Route::post('/api-controller', 'generateApiController')->name('create-api-controller-command');
            Route::post('/test', 'generateTest')->name('create-test-command');
            Route::post('/postman-collection', 'callCreatePostmanCollectionCommand')->name('create-postman-collection-command');
            Route::post('/web-controller', 'generateWebController')->name('create-web-controller-command');
            Route::post('/add-actor', 'generateActors')->name('add-actor-command');
            Route::post('/generate-model' , 'generateModel')->name('generate-model');
        });

        Route::get('/install-web-packages', 'installWebPackages')->name('install-web-packages');
        Route::get('/auth-init/{container?}', 'installAuth')->name('init-auth');
    });
});
