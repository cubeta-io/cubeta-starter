<?php

use Illuminate\Support\Facades\Route;
use Cubeta\CubetaStarter\app\Http\Controllers\CallAppropriateCommand;
use Cubeta\CubetaStarter\app\Http\Controllers\RenderAppropriateViewController;

Route::prefix('/cubeta-starter')->name('cubeta-starter.')->group(function () {
    Route::view('/', 'CubetaStarter::complete-install')->name('complete-installation');
    Route::view('/output', 'CubetaStarter::command-output')->name('output');

    Route::controller(RenderAppropriateViewController::class)->group(function () {
        Route::get('/docs', 'getDocumentation')->name('get-documentation');

        Route::prefix('generate')->name('generate-')->group(function () {
            Route::get('/', 'fullGenerate')->name('full.page');
            Route::get('/migration', 'generateMigration')->name('migration.page');
            Route::get('/factory', 'generateFactory')->name('factory.page');
            Route::get('/seeder', 'generateSeeder')->name('seeder.page');
            Route::get('/repository', 'generateRepository')->name('repository.page');
            Route::get('/service', 'generateService')->name('service.page');
            Route::get('/request', 'generateRequest')->name('request.page');
            Route::get('/resource', 'generateResource')->name('resource.page');
            Route::get('/api-controller', 'generateController')->name('api-controller.page');
            Route::get('/test', 'generateTest')->name('test.page');
            Route::get('/policy', 'generatePolicy')->name('policy.page');
            Route::get('/postman-collection', 'generatePostmanCollection')->name('postman-collection.page');
            Route::get('/add-actor', 'addActor')->name('add-actor.page');
            Route::get('/web-controller', 'generateWebController')->name('web-controller.page');
        });
    });

    Route::controller(CallAppropriateCommand::class)->group(function () {

        Route::prefix('/publishes')->group(function () {
            Route::get('/service-providers', 'publishProviders')->name('publish-providers');
            Route::get('/testing-tools', 'publishTestingTools')->name('publish-testing-tools');
            Route::get('/publish-config', 'publishConfig')->name('config-publish');
            Route::get('/publish-assets', 'publishAssets')->name('publish-assets');
            Route::get('/publish-response-handlers', 'callPublishResponseHandlers')->name('publish-response-handlers');
            Route::get('/publish-crud-handlers', 'callPublishCrudHandlers')->name('publish-crud-handlers');
            Route::get('/publish-locale-handlers', 'callPublishLocaleHandler')->name('publish-locale-handlers');
            Route::get('/all', 'publishAll')->name('publish-all');
        });

        Route::prefix('make')->name('call-')->group(function () {
            Route::post('/full-generate', 'callCreateModelCommand')->name('create-model-command');
            Route::post('/migration', 'callCreateMigrationCommand')->name('create-migration-command');
            Route::post('/factory', 'callCreateFactoryCommand')->name('create-factory-command');
            Route::post('/seeder', 'callCreateSeederCommand')->name('create-seeder-command');
            Route::post('/repository', 'callCreateRepositoryCommand')->name('create-repository-command');
            Route::post('/service', 'callCreateServiceCommand')->name('create-service-command');
            Route::post('/request', 'callCreateRequestCommand')->name('create-request-command');
            Route::post('/resource', 'callCreateResourceCommand')->name('create-resource-command');
            Route::post('/api-controller', 'callCreateApiControllerCommand')->name('create-api-controller-command');
            Route::post('/test', 'callCreateTestCommand')->name('create-test-command');
            Route::post('/policy', 'callCreatePolicyCommand')->name('create-policy-command');
            Route::post('/postman-collection', 'callCreatePostmanCollectionCommand')->name('create-postman-collection-command');
            Route::post('/web-controller', 'callCreateWebControllerCommand')->name('create-web-controller-command');
            Route::post('/add-actor', 'callAddActorCommand')->name('add-actor-command');
        });

        Route::get('/install-spatie', 'callInstallSpatie')->name('call-install-spatie');
        Route::get('/install-web-packages', 'installingWebPackages')->name('install-web-packages');
        Route::get('/auth-init/{container?}', 'initAuth')->name('init-auth');
    });
});
