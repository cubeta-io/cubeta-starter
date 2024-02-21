<?php

return [

    /**
     * the created postman collection will be named corresponding to it
     */
    'project_name' => 'CubetaStarter',

    /**
     * here define your project public url, so we can add it to the postman collection
     * if you let it null we will place it in the collection as below :
     * e.g : http://localhost/project-name-from-this-config/public/
     */
    'project_url' => null,

    /**
     * model path and namespace
     */
    'model_namespace' => "App\Models",
    'model_path' => 'app/Models',

    /**
     * repositories path and namespace
     */
    'repository_namespace' => "App\Repositories",
    'repository_path' => 'app/Repositories',

    /**
     * service path and namespace
     */
    'service_namespace' => 'App\Services',
    'service_path' => 'app/Services',

    /**
     * api controller path and namespace
     */
    'api_controller_namespace' => 'App\Http\Controllers\API\v1',
    'api_controller_path' => 'app/Http/Controllers/API/v1',

    /**
     * web controller path and namespace
     */
    'web_controller_namespace' => 'App\Http\Controllers\WEB\v1',
    'web_controller_path' => 'app/Http/Controllers/WEB/v1',

    /**
     * requests path and namespace
     */
    'request_namespace' => 'App\Http\Requests',
    'request_path' => 'app/Http/Requests',

    /**
     * resources path and namespace
     */
    'resource_namespace' => 'App\Http\Resources',
    'resource_path' => 'app/Http/Resources',

    /**
     * policy path and namespace
     */
    'policy_namespace' => 'App\Policies',
    'policy_path' => 'app/Policies',

    /**
     * the directory of all migration files
     */
    'migration_path' => 'database/migrations',

    /**
     * seeders path and namespace
     */
    'seeder_namespace' => 'Database\Seeders',
    'seeder_path' => 'database/seeders',

    /**
     * factory path and namespace
     */
    'factory_namespace' => 'Database\Factories',
    'factory_path' => 'database/factories',

    /**
     * test path and namespace
     */
    'test_namespace' => 'Tests\Feature',
    'test_path' => 'tests/Feature',

    'trait_namespace' => 'App\Traits',
    'trait_path' => 'app\Traits',

    'exception_namespace' => 'App\Exceptions',
    'exception_path' => 'app\Exceptions',

    /**
     * postman collection path
     */
    'postman_collection _path' => '',  // empty string mean it will be in the root directory

    /**
     * the project available languages
     */
    'available_locales' => [
        'en',
        // add your project languages
    ],

    /**
     * your project default locale
     */
    'defaultLocale' => 'en', // consider to make all values corresponding to this locale not null it is better
];
