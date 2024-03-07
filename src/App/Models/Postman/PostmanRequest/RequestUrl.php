<?php

namespace Cubeta\CubetaStarter\App\Models\Postman\PostmanRequest;

use Cubeta\CubetaStarter\App\Models\Postman\PostmanObject;
use Illuminate\Support\Collection;

class RequestUrl implements PostmanObject
{
    public string $raw;
    public array $host = [
        "{{local}}v1"
    ];

    public static function getUrlFromRoute(string $route): RequestUrl
    {
        return new self("{{local}}v1/$route");
    }

    /**
     * @param string $raw
     */
    public function __construct(string $raw)
    {
        $this->raw = $raw;
        $this->host = [
            $raw,
        ];
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
