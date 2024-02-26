<?php

namespace Cubeta\CubetaStarter\app\Models\Postman;

use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\PostmanRequest;
use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\RequestUrl;
use Illuminate\Support\Collection;

class PostmanCollection implements PostmanObject
{
    public string $name;

    /** @var PostmanItem[] */
    public array $items = [];

    /** @var PostmanVariable[] */
    public array $variables = [];

    /** @var PostmanEvent[] */
    public array $events = [];

    public string $scheme = 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json';

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


    /**
     * @param array $data
     * @return self
     */
    public static function serialize(array $data): PostmanCollection
    {
        return new self(
            $data['name'] ?? '',
            array_map(fn($item) => PostmanItem::serialize($item), $data['item']),
            array_map(fn($variable) => PostmanVariable::serialize($variable), $data['variable'] ?? []),
            array_map(fn($event) => PostmanEvent::serialize($event), $data['event'] ?? []),
        );
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        return [
            'info' => ["name" => $this->name, "schema" => $this->scheme],
            'item' => array_map(fn(PostmanItem $item) => $item->toArray(), $this->items ?? []),
            'event' => array_map(fn($event) => $event->toArray(), $this->events),
            'variables' => array_map(fn($var) => $var->toArray(), $this->variables),
        ];
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    public function addFolder(): static
    {
        $this->items[] = new PostmanItem('rate',
            [
                new PostmanRequest(
                    'get all',
                    'GET',
                    [],
                    [],
                    null,
                    new RequestUrl('rate'),
                    null,
                    [],
                )
            ]
        );

        return $this;
    }
}
