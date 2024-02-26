<?php

namespace Cubeta\CubetaStarter\app\Models\Postman;

use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\FormDataField;
use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\PostmanRequest;
use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\RequestAuth;
use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\RequestBody;
use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\RequestHeader;
use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\RequestUrl;
use Illuminate\Support\Collection;
use Mockery\Exception;

class PostmanCollection implements PostmanObject
{
    public string $name;

    /** @var PostmanItem[] */
    public array $items = [];

    /** @var PostmanVariable[] */
    public array $variables = [];

    /** @var PostmanEvent[] */
    public array $events = [];

    public string $scheme = "https://schema.getpostman.com/json/collection/v2.1.0/collection.json";

    /**
     * @param string $name
     * @param PostmanItem[] $items
     * @param PostmanVariable[] $variables
     * @param PostmanEvent[] $events
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

    public function newCrud(string $modelName, string $route, array $columns = []): static
    {
        $index = new PostmanRequest(
            name: "index",
            method: PostmanRequest::GET,
            url: new RequestUrl($route),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer(),
        );

        $show = new PostmanRequest(
            name: "show",
            method: PostmanRequest::GET,
            url: new RequestUrl("$route/1"),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer()
        );

        $store = new PostmanRequest(
            name: "store",
            method: PostmanRequest::POST,
            url: new RequestUrl("$route"),
            header: [RequestHeader::setAcceptJson()],
            body: new RequestBody('formdata', $this->getBodyData($columns)),
            auth: RequestAuth::bearer(),
        );

        $update = new PostmanRequest(
            name: "update",
            method: PostmanRequest::PUT,
            url: new RequestUrl("$route"),
            header: [RequestHeader::setAcceptJson()],
            body: new RequestBody('formdata', $this->getBodyData($columns)),
            auth: RequestAuth::bearer(),
        );

        $delete = new PostmanRequest(
            name: "delete",
            method: PostmanRequest::DELETE,
            url: new RequestUrl("$route"),
            header: [RequestHeader::setAcceptJson()],
            auth: RequestAuth::bearer(),
        );

        $this->items[] = new PostmanItem($modelName, [$index, $show, $store, $update, $delete]);

        return $this;
    }

    /**
     * @param array $columns
     * @return FormDataField[]
     */
    private function getBodyData(array $columns): array
    {
        $data = [];
        foreach ($columns as $column => $type) {
            $data[] = match ($type) {
                "boolean" => new FormDataField($column, (string)fake()->boolean),
                "date" => new FormDataField($column, now()->format('Y-m-d')),
                "datetime" => new FormDataField($column, now()->format('Y-m-d H:i:s')),
                'time' => new FormDataField($column, now()->format('H:i:s')),
                'integer' | 'bigInteger' | 'unsignedBigInteger' | 'unsignedDouble' | 'double' | 'float' => new FormDataField($column, (string)fake()->numberBetween(1, 10)),
                'json' => new FormDataField($column, (string)json_encode([fake()->word => fake()->word])),
                'translatable' => new FormDataField($column, (string)json_encode(["ar" => fake()->word, "en" => fake()->word])),
                'text' => new FormDataField($column, fake()->text),
                'key' => new FormDataField($column, "1"),
                default => new FormDataField($column, fake()->word),
            };
        }

        return $data;
    }

    public function newAuthApi(string $role)
    {
        $apiStub = file_get_contents(__DIR__ . '/../../../Commands/stubs/Auth/auth-postman-entity.stub');
        $api = str_replace("{role}", $role, $apiStub);
        $this->items[] = PostmanItem::serialize(json_decode($api, true));
        return $this;
    }

    /**
     * @param array $data
     * @return self
     */
    public static function serialize(array $data): PostmanCollection
    {
        try {
            return new self(
                $data['info']['name'] ?? '',
                array_map(fn($item) => PostmanItem::serialize($item), $data['item']),
                array_map(fn($variable) => PostmanVariable::serialize($variable), $data['variable'] ?? []),
                array_map(fn($event) => PostmanEvent::serialize($event), $data['event'] ?? [])
            );
        } catch (\Exception $exception) {
            dd($data, $exception->getMessage());
        }
    }

    /**
     * save the collection
     * @return $this
     */
    public function save(): static
    {
        if (!Postman::$path) throw new Exception("Collection Path isn\t specified");
        file_put_contents(Postman::$path, json_encode($this->toArray(), JSON_UNESCAPED_SLASHES));
        return $this;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
