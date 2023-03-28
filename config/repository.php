<?php

return [
    /**
     * The directory for all the repositories
     */
    "repository_directory" => "app/Repositories",

    /**
     * Default repository namespace
     */
    "repository_namespace" => "App\Repositories",

    /**
     * The directory for all the services
     */
    "service_directory" => "app/Services",
    "model_directory" => "app/Models",

    /**
     * Default service namespace
     */
    "service_namespace" => "App\Services",
    "model_namespace" => "App\Models",

    /**
     * Default repository implementation
     */
    "default_repository_implementation" => "Eloquent",

    /**
     * Current repository implementation
     */
    "current_repository_implementation" => "Eloquent",

    /**
     * Repository interface name suffix
     */
    "repository_interface_suffix" => "RepositoryInterface",

    /**
     * Repository name suffix
     */
    "repository_suffix" => "Repository",

    /**
     * Service name suffix
     */
    "service_suffix" => "Service"
];
