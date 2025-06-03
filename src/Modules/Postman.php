<?php

namespace Cubeta\CubetaStarter\Modules;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\CubeCollection;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;
use Illuminate\Support\Collection;

class Postman
{
    use RouteBinding;

    /**
     * Postman collection schema version
     */
    private string $schema = "https://schema.getpostman.com/json/collection/v2.1.0/collection.json";

    /**
     * Collection name
     */
    private string $name;

    /**
     * path for saving
     */
    private CubePath $path;

    /**
     * Collection items (folders and requests)
     */
    private array $items;

    /**
     * Collection variables
     */
    private array $variables;

    /**
     * Collection events
     */
    private array $events;

    /**
     * Collection actors
     */
    private array $actors = [];

    /**
     * Constructor
     * @param string $name The name of the collection
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->path = CubePath::make(config('cubeta-starter.postman_collection_path') . $this->name . ".postman_collection.json");
        $collection = $this->path->exist() ? $this->path->getContent() : "[]";
        $collection = json_decode($collection, true);
        $this->items = $collection['item'] ?? [];
        $this->events = $collection['event'] ?? [];

        $this->variables = $collection['variable'] ?? [
            ['key' => 'local', 'value' => $this->getProjectUrl(), 'type' => 'string'],
            ['key' => 'token', 'value' => '', 'type' => 'string'],
            ['key' => 'locale', 'value' => 'en', 'type' => 'string'],
            ['key' => 'fakeJson', 'value' => '{"en":"test" , "ar":"تجريب"}', 'type' => 'string']
        ];
    }

    /**
     * Create a new collection instance
     * @param string|null $name Optional collection name
     * @return self
     */
    public static function make(?string $name = null): self
    {
        $name = $name ?? config('cubeta-starter.project_name');
        return new self($name);
    }

    /**
     * Add CRUD API endpoints for a model to the collection
     * @param CubeTable   $table The model table configuration
     * @param string|null $actor The actor (user role) for these endpoints
     * @return self
     */
    public function addCrud(CubeTable $table, ?string $actor = null): self
    {
        // Skip if this CRUD already exists
        if ($this->collectionExists($table->modelName, $actor)) {
            return $this;
        }

        $baseUrl = $table->resourceRoute($actor)->url;

        if ((empty($actor) || $actor == "none")) {
            if (Settings::make()->installedApiAuth()) {
                $actor = "protected";
            } else {
                $actor = "public";
            }
        }

        $endpoints = [];

        // Index endpoint
        $endpoints[] = $this->createRequest(
            "index",
            "GET",
            $baseUrl,
            true
        );

        // Show endpoint
        $endpoints[] = $this->createRequest(
            "show",
            "GET",
            "{$baseUrl}/1",
            true
        );

        // Store endpoint
        $endpoints[] = $this->createRequest(
            "store",
            "POST",
            $baseUrl,
            true,
            $this->generateFormData($table->attributes()),
        );

        // Update endpoint
        $endpoints[] = $this->createRequest(
            "update",
            "PUT",
            "{$baseUrl}/1",
            true,
            $this->generateFormData($table->attributes())
        );

        // Delete endpoint
        $endpoints[] = $this->createRequest(
            "delete",
            "DELETE",
            "{$baseUrl}/1",
            true
        );

        // Export endpoint
        $endpoints[] = $this->createRequest(
            "export",
            "POST",
            "{$baseUrl}/export",
            true
        );

        // Import endpoint
        $endpoints[] = $this->createRequest(
            "import",
            "POST",
            "{$baseUrl}/import",
            true
        );

        // Import example endpoint
        $endpoints[] = $this->createRequest(
            "import-example",
            "GET",
            "{$baseUrl}/get-import-example",
            true
        );

        // Add the endpoints to a folder
        $folder = [
            'name' => $table->modelName,
            'item' => $endpoints
        ];

        $this->addToActorFolder($actor, $folder);
        return $this;
    }

    /**
     * Add authentication API endpoints for a user role
     * @param string $role The user role (actor)
     * @return self
     */
    public function addAuthApi(string $role): self
    {
        // Skip if auth endpoints for this role already exist
        if ($this->collectionExists("$role auth", $role)) {
            return $this;
        }

        // Create auth endpoints folder
        $authFolder = [
            'name' => "auth",
            'item' => [
                // Register endpoint
                $this->createRequest(
                    "register",
                    "POST",
                    Routes::register(ContainerType::API, $role)->url,
                    true,
                    [
                        ['key' => 'first_name', 'value' => $role, 'type' => 'text'],
                        ['key' => 'last_name', 'value' => $role, 'type' => 'text'],
                        ['key' => 'email', 'value' => "$role@email.com", 'type' => 'text'],
                        ['key' => 'password', 'value' => "123456789", 'type' => 'text'],
                        ['key' => 'password_confirmation', 'value' => "123456789", 'type' => 'text']
                    ],
                    [
                        [
                            'listen' => 'test',
                            'script' => [
                                'exec' => [
                                    "var jsonData = JSON.parse(responseBody);",
                                    "pm.collectionVariables.set(\"token\", jsonData.data.token);"
                                ],
                                'type' => 'text/javascript'
                            ]
                        ]
                    ]
                ),

                // Update info endpoint
                $this->createRequest(
                    "update info",
                    "POST",
                    Routes::updateUser(ContainerType::API, $role)->url,
                    true,
                    [
                        ['key' => 'first_name', 'value' => $role, 'type' => 'text'],
                        ['key' => 'last_name', 'value' => $role, 'type' => 'text'],
                        ['key' => 'email', 'value' => "$role@email.com", 'type' => 'text'],
                        ['key' => 'password', 'value' => "123456789", 'type' => 'text'],
                        ['key' => 'password_confirmation', 'value' => "123456789", 'type' => 'text'],
                        ['key' => 'fcm_token', 'value' => "", 'type' => 'text']
                    ]
                ),

                // Login endpoint
                $this->createRequest(
                    "login",
                    "POST",
                    Routes::login(ContainerType::API, $role)->url,
                    true,
                    [
                        ['key' => 'email', 'value' => "$role@email.com", 'type' => 'text'],
                        ['key' => 'password', 'value' => "123456789", 'type' => 'text']
                    ],
                    [
                        [
                            'listen' => 'test',
                            'script' => [
                                'exec' => [
                                    "var jsonData = JSON.parse(responseBody);",
                                    "pm.collectionVariables.set(\"token\", jsonData.data.token);"
                                ],
                                'type' => 'text/javascript'
                            ]
                        ]
                    ]
                ),

                // Refresh token endpoint
                $this->createRequest(
                    "refresh",
                    "POST",
                    Routes::refreshToken($role)->url,
                    true
                ),

                // Reset password request endpoint
                $this->createRequest(
                    "reset password request",
                    "POST",
                    Routes::requestResetPassword(ContainerType::API, $role)->url,
                    true,
                    [
                        ['key' => 'email', 'value' => "$role@email.com", 'type' => 'text']
                    ],
                    [],
                    'urlencoded'
                ),

                // Check reset password code endpoint
                $this->createRequest(
                    "check reset password code",
                    "POST",
                    Routes::validateResetCode(ContainerType::API, $role)->url,
                    true,
                    [
                        ['key' => 'reset_password_code', 'value' => "", 'type' => 'text']
                    ]
                ),

                // Reset password endpoint
                $this->createRequest(
                    "reset password",
                    "POST",
                    Routes::resetPassword(ContainerType::API, $role)->url,
                    true,
                    [
                        ['key' => 'reset_password_code', 'value' => "", 'type' => 'text'],
                        ['key' => 'password', 'value' => "12345678", 'type' => 'text'],
                        ['key' => 'password_confirmation', 'value' => "12345678", 'type' => 'text']
                    ]
                ),

                // Logout endpoint
                $this->createRequest(
                    "logout",
                    "POST",
                    Routes::logout(ContainerType::API, $role)->url,
                    true
                ),

                // User details endpoint
                $this->createRequest(
                    "user details",
                    "GET",
                    Routes::me(ContainerType::API, $role)->url,
                    true
                )
            ]
        ];

        // Add auth folder to actor folder
        $this->addToActorFolder($role, $authFolder);

        return $this;
    }

    /**
     * Add a folder to an actor's folder
     * @param string $actor  The actor name
     * @param array  $folder The folder to add
     * @return void
     */
    private function addToActorFolder(string $actor, array $folder): void
    {
        // Check if the actor folder exists
        $actorFolderIndex = $this->findActorFolderIndex($actor);

        if ($actorFolderIndex !== false) {
            // Actor folder exists, add folder to it
            $this->items[$actorFolderIndex]['item'][] = $folder;
        } else {
            // Create a new actor folder with this folder inside
            $this->items[] = [
                'name' => $actor,
                'item' => [$folder]
            ];

            // Track actor for future reference
            $this->actors[] = $actor;
        }
    }

    /**
     * Find the index of an actor's folder in the item array
     * @param string $actor The actor name
     * @return int|false The index of the actor folder or false if not found
     */
    private function findActorFolderIndex(string $actor): bool|int
    {
        foreach ($this->items as $index => $item) {
            if ($item['name'] === $actor) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Create a Postman request
     * @param string $name     Request name
     * @param string $method   HTTP method (GET, POST, PUT, DELETE)
     * @param string $url      Request URL
     * @param bool   $auth     Whether to include bearer auth
     * @param array  $formData Form data for the request body
     * @param array  $events   Request events
     * @param string $bodyMode Body mode (formdata or urlencoded)
     * @return array
     */
    private function createRequest(
        string $name,
        string $method,
        string $url,
        bool   $auth = false,
        array  $formData = [],
        array  $events = [],
        string $bodyMode = 'formdata'
    ): array
    {
        $url = str($url)->startsWith("/")
            ? str($url)->replaceFirst('/', '')
            : $url;

        $request = [
            'name' => $name,
            'request' => [
                'method' => $method,
                'header' => [
                    [
                        'key' => 'Accept',
                        'value' => 'application/json',
                        'type' => 'text'
                    ]
                ],
                'url' => [
                    'raw' => "{{local}}/{$url}",
                    "host" => [
                        "{{local}}/{$url}"
                    ]
                ]
            ]
        ];

        // Add auth if needed
        if ($auth) {
            $request['request']['auth'] = [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{token}}',
                        'type' => 'string'
                    ]
                ]
            ];
        }

        // Add body if form data exists
        if (!empty($formData)) {
            $request['request']['body'] = [
                'mode' => $bodyMode,
                $bodyMode => $formData
            ];
        }

        // Add events if any
        if (!empty($events)) {
            $request['event'] = $events;
        }

        return $request;
    }

    /**
     * Check if a collection with the given name already exists
     * @param string      $name  Collection name
     * @param string|null $actor Actor name if checking within an actor folder
     * @return bool
     */
    public function collectionExists(string $name, ?string $actor = null): bool
    {
        if ($actor) {
            $actorFolderIndex = $this->findActorFolderIndex($actor);
            if ($actorFolderIndex === false) {
                return false;
            }

            foreach ($this->items[$actorFolderIndex]['item'] as $item) {
                if ($item['name'] === $name) {
                    return true;
                }
            }
            return false;
        }

        foreach ($this->items as $item) {
            if ($item['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate form data for a request based on model attributes
     * @param CubeCollection<CubeAttribute>|array $columns Model attributes
     * @return array
     */
    private function generateFormData(Collection|array $columns): array
    {
        $data = [];
        foreach ($columns as $column) {
            if ($column instanceof HasPropertyValidationRule) {
                $rules = implode(",", $column->propertyValidationRule()->rules);
            } else {
                $rules = "";
            }

            if ($column->isNumeric()) {
                $data[] = ['key' => $column->name, 'value' => (string)fake()->numberBetween(1, 10), 'type' => 'text', 'description' => $rules];
                continue;
            }

            $data[] = match ($column->type) {
                ColumnTypeEnum::BOOLEAN->value => ['key' => $column->name, 'value' => (int)fake()->boolean, 'type' => 'text', 'description' => $rules],
                ColumnTypeEnum::DATE->value => ['key' => $column->name, 'value' => now()->format('Y-m-d'), 'type' => 'text', 'description' => $rules],
                ColumnTypeEnum::DATETIME->value => ['key' => $column->name, 'value' => now()->format('Y-m-d H:i:s'), 'type' => 'text', 'description' => $rules],
                ColumnTypeEnum::TIME->value => ['key' => $column->name, 'value' => now()->format('H:i:s'), 'type' => 'text', 'description' => $rules],
                ColumnTypeEnum::JSON->value => ['key' => $column->name, 'value' => (string)json_encode([fake()->word => fake()->word]), 'type' => 'text', 'description' => $rules],
                ColumnTypeEnum::TRANSLATABLE->value => ['key' => $column->name, 'value' => (string)json_encode(["ar" => fake()->word, "en" => fake()->word]), 'type' => 'text', 'description' => $rules],
                ColumnTypeEnum::TEXT->value => ['key' => $column->name, 'value' => fake()->text, 'type' => 'text', 'description' => $rules],
                ColumnTypeEnum::KEY->value => ['key' => $column->name, 'value' => "1", 'type' => 'text', 'description' => $rules],
                default => ['key' => $column->name, 'value' => fake()->word, 'type' => 'text', 'description' => $rules],
            };
        }

        return $data;
    }

    /**
     * Get the project URL for the collection
     * @return string
     */
    private function getProjectUrl(): string
    {
        $url = config('cubeta-starter.project_url') ?? "http://localhost/" . config('cubeta-starter.project_name') . "/public/api";
        return str($url)->endsWith("/")
            ? str($url)->replaceLast('/', '')
            : $url;
    }

    /**
     * Get a collection as an array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'info' => [
                'name' => $this->name,
                'schema' => $this->schema
            ],
            'item' => $this->items,
            'event' => $this->events,
            'variable' => $this->variables
        ];
    }

    /**
     * Save the collection to a file
     * @return self
     * @throws Exception
     */
    public function save(): self
    {
        $this->path->ensureDirectoryExists();
        $this->path->putContent(json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        return $this;
    }
}
