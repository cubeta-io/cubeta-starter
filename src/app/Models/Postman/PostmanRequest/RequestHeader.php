<?php

namespace Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest;

use Cubeta\CubetaStarter\app\Models\Postman\PostmanObject;
use Illuminate\Support\Collection;

class RequestHeader implements PostmanObject
{
    public ?string $key = null ;
    public ?string $value = null;
    public ?string $type = 'text';

    /**
     * @param string|null $key
     * @param string|null $value
     * @param string|null $type
     */
    public function __construct(?string $key, ?string $value, ?string $type)
    {
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return $this
     */
    public function setAcceptJson(): static
    {
        $this->key = 'Accept';
        $this->type = 'text';
        $this->value = 'application/json';
        return $this;
    }

    public function toArray(): array
    {
        return[
            'key' => $this->key ,
            'value' => $this->value ,
            'type' => $this->type,
        ];
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray() , JSON_PRETTY_PRINT);
    }

    /**
     * @param array{key:string , value:string , type:string} $data
     * @return self
     */
    public static function serialize(array $data)
    {
        return new self($data['key'] , $data['value'] , $data['type'] ?? 'text');
    }
}
