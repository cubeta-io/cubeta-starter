<?php

namespace Cubeta\CubetaStarter\App\Models\Postman\PostmanRequest;

use Cubeta\CubetaStarter\App\Models\Postman\PostmanObject;
use Illuminate\Support\Collection;

class RequestAuth implements PostmanObject
{
    public string $type = 'bearer';

    /** @var array{key:string,value:string,type:string}|array[] */
    public array $bearer;

    /**
     * @param string $type
     * @param array|array[] $bearer
     */
    public function __construct(string $type, array $bearer)
    {
        $this->type = $type;
        $this->bearer = $bearer;
    }

    public static function bearer(): RequestAuth
    {
        return new self('bearer', [
            'key' => 'token',
            'value' => '{{token}}',
            'type' => 'string'
        ]);
    }

    /**
     * @param array{type:string|null , bearer:string|null} $data
     * @return self
     */
    public static function serialize(array $data): RequestAuth
    {
        return new self($data['type'] ?? 'bearer', $data['bearer'] ?? []);
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array{type:string , bearer:array{key:string,value:string,type:string}}
     */
    public function toArray(): array
    {
        return [
            "type" => $this->type,
            "{$this->type}" => [$this->bearer]
        ];
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
