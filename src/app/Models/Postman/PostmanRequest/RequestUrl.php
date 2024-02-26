<?php

namespace Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest;

use Cubeta\CubetaStarter\app\Models\Postman\PostmanObject;
use Illuminate\Support\Collection;

class RequestUrl implements PostmanObject
{
    public string $raw;
    public array $host = [
        "{{local}}v1"
    ];

    /**
     * @param string $raw
     */
    public function __construct(string $raw)
    {
        $this->raw = $raw;
    }

    /**
     * @param array{raw:string} $data
     * @return self
     */
    public static function serialize(array $data): RequestUrl
    {
        return new self($data['raw']);
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array{raw:string , host:string[]}
     */
    public function toArray(): array
    {
        return [
            'raw' => $this->raw,
            'host' => $this->host
        ];
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
