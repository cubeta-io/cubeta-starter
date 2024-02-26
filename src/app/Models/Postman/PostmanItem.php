<?php

namespace Cubeta\CubetaStarter\app\Models\Postman;

use Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest\PostmanRequest;
use Exception;
use Illuminate\Support\Collection;

class PostmanItem implements PostmanObject
{
    public string $name;

    /** @var PostmanItem[]|PostmanRequest[]|null */
    public ?array $items;

    /**
     * @param string $name
     * @param PostmanItem[]|PostmanRequest[]|null $items
     */
    public function __construct(string $name, ?array $items)
    {
        $this->name = $name;
        $this->items = $items;
    }

    public static function serialize(array $data)
    {
        try {
            if (isset($data['item'])) {
                return new self(
                    $data['name'],
                    array_map(
                        function ($item) {
                            if (isset($item['item'])) {
                                return PostmanItem::serialize($item);
                            } else {
                                return PostmanRequest::serialize($item);
                            }
                        },
                        $data['item']
                    )
                );
            } else if (isset($data['request'])) {
                return new self($data['name'], [PostmanRequest::serialize($data)]);
            };
        } catch (Exception $e) {
            dd($data, $e->getMessage(), $e->getLine(), $e->getFile());
        }
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'item' => array_merge(
                array_map(fn($folder) => $folder->toArray(), $this->items ?? []),
                array_map(fn($req) => $req->toArray(), $this->requests ?? [])
            )
        ];
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }
}
