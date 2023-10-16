<?php

use Illuminate\Support\Facades\Route;
use Cubeta\CubetaStarter\app\Http\Controllers\CallAppropriateCommand;
use Cubeta\CubetaStarter\app\Http\Controllers\RenderAppropriateViewController;

Route::prefix('/cubeta-starter')->name('cubeta-starter.')->group(function () {
    Route::view('/', 'CubetaStarter::complete-install')->name('complete-installation');
    Route::view('/output', 'CubetaStarter::command-output')->name('output');
    Route::get('/docs', [RenderAppropriateViewController::class, 'getDocumentation'])->name('get-documentation');

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

    Route::prefix('/publishes')->group(function () {
        Route::get('/repositories', [CallAppropriateCommand::class, 'publishRepositories'])->name('repositories-publish');
        Route::get('/services', [CallAppropriateCommand::class, 'publishServices'])->name('publish-services');
        Route::get('/api-controller', [CallAppropriateCommand::class, 'publishApiController'])->name('publish-api-controller');
        Route::get('/middlewares', [CallAppropriateCommand::class, 'publishMiddlewares'])->name('publish-middlewares');
        Route::get('/traits', [CallAppropriateCommand::class, 'publishTraits'])->name('publish-traits');
        Route::get('validation-rules', [CallAppropriateCommand::class, 'publishValidationRules'])->name('publish-validation-rules');
        Route::get('/service-providers', [CallAppropriateCommand::class, 'publishProviders'])->name('publish-providers');
        Route::get('/testing-tools', [CallAppropriateCommand::class, 'publishTestingTools'])->name('publish-testing-tools');

        Route::get('/publish-config', [CallAppropriateCommand::class, 'publishConfig'])->name('config-publish');
        Route::get('/publish-handler', [CallAppropriateCommand::class, 'publishHandler'])->name('publish-handler');
        Route::get('publish-assets', [CallAppropriateCommand::class, 'publishAssets'])->name('publish-assets');

        Route::get('/all', [CallAppropriateCommand::class, 'publishAll'])->name('publish-all');
    });

    Route::prefix('make')->name('call-')->group(function () {
        Route::post('/full-generate', [CallAppropriateCommand::class, 'callCreateModelCommand'])->name('create-model-command');
        Route::post('/migration', [CallAppropriateCommand::class, 'callCreateMigrationCommand'])->name('create-migration-command');
        Route::post('/factory', [CallAppropriateCommand::class, 'callCreateFactoryCommand'])->name('create-factory-command');
        Route::post('/seeder', [CallAppropriateCommand::class, 'callCreateSeederCommand'])->name('create-seeder-command');
        Route::post('/repository', [CallAppropriateCommand::class, 'callCreateRepositoryCommand'])->name('create-repository-command');
        Route::post('/service', [CallAppropriateCommand::class, 'callCreateServiceCommand'])->name('create-service-command');
        Route::post('/request', [CallAppropriateCommand::class, 'callCreateRequestCommand'])->name('create-request-command');
        Route::post('/resource', [CallAppropriateCommand::class, 'callCreateResourceCommand'])->name('create-resource-command');
        Route::post('/api-controller', [CallAppropriateCommand::class, 'callCreateApiControllerCommand'])->name('create-api-controller-command');
        Route::post('/test', [CallAppropriateCommand::class, 'callCreateTestCommand'])->name('create-test-command');
        Route::post('/policy', [CallAppropriateCommand::class, 'callCreatePolicyCommand'])->name('create-policy-command');
        Route::post('/postman-collection', [CallAppropriateCommand::class, 'callCreatePostmanCollectionCommand'])->name('create-postman-collection-command');
        Route::post('/web-controller', [CallAppropriateCommand::class, 'callCreateWebControllerCommand'])->name('create-web-controller-command');
        Route::post('/add-actor', [CallAppropriateCommand::class, 'callAddActorCommand'])->name('add-actor-command');
    });


    Route::get('/install-spatie', [CallAppropriateCommand::class, 'callInstallSpatie'])->name('install-spatie');
    Route::get('/install-web-packages', [CallAppropriateCommand::class, 'installingWebPackages'])->name('install-web-packages');
    Route::get('/auth-init/{container?}', [CallAppropriateCommand::class, 'initAuth'])->name('init-auth');
});
