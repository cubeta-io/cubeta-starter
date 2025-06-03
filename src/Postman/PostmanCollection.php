<?php

namespace Cubeta\CubetaStarter\Postman;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Postman\PostmanRequest\FormDataField;
use Cubeta\CubetaStarter\Postman\PostmanRequest\PostmanRequest;
use Cubeta\CubetaStarter\Postman\PostmanRequest\RequestAuth;
use Cubeta\CubetaStarter\Postman\PostmanRequest\RequestBody;
use Cubeta\CubetaStarter\Postman\PostmanRequest\RequestHeader;
use Cubeta\CubetaStarter\Postman\PostmanRequest\RequestUrl;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;
use Illuminate\Support\Collection;

class PostmanCollection implements PostmanObject
{
    use RouteBinding;

    public string $name;

    /** @var PostmanItem[] */
    public array $items = [];

    /** @var PostmanVariable[] */
    public array $variables = [];

    /** @var PostmanEvent[] */
    public array $events = [];

    public string $scheme = "https://schema.getpostman.com/json/collection/v2.1.0/collection.json";

    /**
     * @param string            $name
     * @param PostmanItem[]     $items
     * @param PostmanVariable[] $variables
     * @param PostmanEvent[]    $events
     */
    public function __construct(string $name, array $items, array $variables, array $events)
    {
        $this->name = $name;
        $this->items = $items;
        $this->variables = $variables;
        $this->events = $events;
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'info' => ["name" => $this->name, "schema" => $this->scheme],
            'item' => array_map(fn(PostmanItem $item) => $item->toArray(), $this->items ?? []),
            'event' => array_map(fn($event) => $event->toArray(), $this->events),
            'variable' => array_map(fn($var) => $var->toArray(), $this->variables),
        ];
    }

    public function newCrud(CubeTable $table, ?string $actor = null): static
    {
        if ($this->checkIfCollectionExist($table->modelName)) {
            return $this;
        }

        $baseUrl = $table->resourceRoute($actor)->url;

        $index = new PostmanRequest(
            name: "index",
            method: PostmanRequest::GET,
            url: RequestUrl::getUrlFromRoute($baseUrl),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer(),
        );

        $show = new PostmanRequest(
            name: "show",
            method: PostmanRequest::GET,
            url: RequestUrl::getUrlFromRoute("{$baseUrl}/1"),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer()
        );

        $store = new PostmanRequest(
            name: "store",
            method: PostmanRequest::POST,
            url: RequestUrl::getUrlFromRoute("{$baseUrl}"),
            header: [RequestHeader::setAcceptJson()],
            body: new RequestBody('formdata', $this->getBodyData($table->attributes())),
            auth: RequestAuth::bearer(),
        );

        $update = new PostmanRequest(
            name: "update",
            method: PostmanRequest::PUT,
            url: RequestUrl::getUrlFromRoute("{$baseUrl}/1"),
            header: [RequestHeader::setAcceptJson()],
            body: new RequestBody('formdata', $this->getBodyData($table->attributes())),
            auth: RequestAuth::bearer(),
        );

        $delete = new PostmanRequest(
            name: "delete",
            method: PostmanRequest::DELETE,
            url: RequestUrl::getUrlFromRoute("{$baseUrl}/1"),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer(),
        );

        $export = new PostmanRequest(
            name: "export",
            method: PostmanRequest::POST,
            url: RequestUrl::getUrlFromRoute("{$baseUrl}/export"),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer(),
        );

        $import = new PostmanRequest(
            name: "import",
            method: PostmanRequest::POST,
            url: RequestUrl::getUrlFromRoute("{$baseUrl}/import"),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer(),
        );

        $exampleImport = new PostmanRequest(
            name: "import-example",
            method: PostmanRequest::GET,
            url: RequestUrl::getUrlFromRoute("{$baseUrl}/get-import-example"),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer(),
        );

        $this->items[] = new PostmanItem($table->modelName, [$index, $show, $store, $update, $delete, $export, $import, $exampleImport]);

        return $this;
    }

    public function checkIfCollectionExist(string $name): bool
    {
        foreach ($this->items as $item) {
            if ($item->name == $name && $item?->items) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Collection<CubeAttribute>|array<CubeAttribute> $columns
     * @return FormDataField[]
     */
    private function getBodyData(Collection|array $columns): array
    {
        $data = [];
        foreach ($columns as $column) {

            if (ColumnTypeEnum::isNumericType($column->type)) {
                $data[] = new FormDataField($column->name, (string)fake()->numberBetween(1, 10));
                continue;
            }

            $data[] = match ($column->type) {
                ColumnTypeEnum::BOOLEAN->value => new FormDataField($column->name, (string)fake()->boolean),
                ColumnTypeEnum::DATE->value => new FormDataField($column->name, now()->format('Y-m-d')),
                ColumnTypeEnum::DATETIME->value => new FormDataField($column->name, now()->format('Y-m-d H:i:s')),
                ColumnTypeEnum::TIME->value => new FormDataField($column->name, now()->format('H:i:s')),
                ColumnTypeEnum::JSON->value => new FormDataField($column->name, (string)json_encode([fake()->word => fake()->word])),
                ColumnTypeEnum::TRANSLATABLE->value => new FormDataField($column->name, (string)json_encode(["ar" => fake()->word, "en" => fake()->word])),
                ColumnTypeEnum::TEXT->value => new FormDataField($column->name, fake()->text),
                ColumnTypeEnum::KEY->value => new FormDataField($column->name, "1"),
                default => new FormDataField($column->name, fake()->word),
            };
        }

        return $data;
    }

    public function newAuthApi(string $role): static
    {
        if ($this->checkIfCollectionExist("$role auth")) {
            return $this;
        }

        $api = FileUtils::generateStringFromStub(CubePath::stubPath('/PostmanCollection/PostmanAuthEntity.stub'), [
            '{{role}}' => $role,
            "{{version}}" => config('cubeta-starter.version'),
            "{{register_url}}" => Routes::register(ContainerType::API, $role)->url,
            '{{update_user_details_url}}' => Routes::updateUser(ContainerType::API, $role)->url,
            '{{login_url}}' => Routes::login(ContainerType::API, $role)->url,
            '{{refresh_token_url}}' => Routes::refreshToken($role)->url,
            '{{request_reset_password_url}}' => Routes::requestResetPassword(ContainerType::API, $role)->url,
            '{{validate_reset_code_url}}' => Routes::validateResetCode(ContainerType::API, $role)->url,
            '{{reset_password_url}}' => Routes::resetPassword(ContainerType::API, $role)->url,
            '{{logout_url}}' => Routes::logout(ContainerType::API, $role)->url,
            '{{user_details_url}}' => Routes::me(ContainerType::API, $role)->url,
        ]);
        $this->items[] = PostmanItem::serialize(json_decode($api, true));
        return $this;
    }

    /**
     * @param array $data
     * @return self
     */
    public static function serialize(array $data): PostmanCollection
    {
        return new self(
            $data['info']['name'] ?? '',
            array_map(fn($item) => PostmanItem::serialize($item), $data['item']),
            array_map(fn($variable) => PostmanVariable::serialize($variable), $data['variable'] ?? []),
            array_map(fn($event) => PostmanEvent::serialize($event), $data['event'] ?? [])
        );
    }

    /**
     * save the collection
     * @return $this
     * @throws Exception
     */
    public function save(): static
    {
        if (!Postman::$path) {
            throw new Exception("Collection Path isn't specified");
        }

        Postman::$path->putContent(json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        return $this;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
