<?php

return [
    /**
     * this project name will appear only in the created postman collection
     */
    'project_name' => "CubetaStarter",

    /**
     * postman collection path
     */
    'postman_collection _path' => '',  // it will be in the root directory

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
     * api controller path and namespace
     */
    'api_controller_namespace' => 'App\Http\Controllers\API\v1',
    'api_controller_path' => 'app/Http/Controllers/API/v1',

    /**
     * model path and namespace
     */
    'model_namespace' => "App\Models",
    'model_path' => 'app/Models',

    /**
     * policy path and namespace
     */
    'policy_namespace' => 'App\Policies',
    'policy_path' => 'app/Policies',

    /**
     * repositories path and namespace
     */
    'repository_namespace' => "App\Repositories",
    'repository_path' => 'app/Repositories',


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
     * service path and namespace
     */
    'service_namespace' => 'App\Services',
    'service_path' => 'app/Services',

    /**
     * test path and namespace
     */
    'test_namespace' => 'Tests\Feature',
    'test_path' => 'tests/Feature',

    'web_controller_namespace' => 'App\Http\Controllers\WEB\v1' ,
    'web_controller_path' => 'app/Http/Controllers/WEB/v1' ,
];
