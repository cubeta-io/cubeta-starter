<?php

namespace Cubeta\CubetaStarter\Postman;

use Illuminate\Support\Collection;

class PostmanVariable implements PostmanObject
{
    public string $key;
    public string $value;
    public string $type = 'string';

    /**
     * @param string $key
     * @param string $value
     * @param string $type
     */
    public function __construct(string $key, string $value, string $type = 'string')
    {
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;
    }

    public static function getLocal(): PostmanVariable
    {
        return new self('local', config('cubeta-starter.project_url'));
    }

    public static function getToken(): PostmanVariable
    {
        return new self('token', '');
    }

    public static function serialize(array $data)
    {
        return new self($data['key'] ?? '', $data['value'] ?? '', $data['type'] ?? 'string');
    }

    public function getFakeJson(): PostmanVariable
    {
        return new self('fakeJson', "{\"en\":\"test\" , \"ar\":\"تجريب\"}");
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type
        ];
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }
}
