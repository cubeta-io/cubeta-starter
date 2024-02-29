<?php

namespace Cubeta\CubetaStarter\App\Models\Postman;

use Illuminate\Support\Collection;

class PostmanEvent implements PostmanObject
{
    const PRE_REQUEST = 'prerequest';
    const TEST = 'test';

    public PostmanScript $script;
    public string $listen;

    /**
     * @param PostmanScript $script
     * @param string $listen = prerequest|test
     */
    public function __construct(PostmanScript $script, string $listen)
    {
        $this->script = $script;
        $this->listen = $listen;
    }

    /**
     * @param array{script:array{exec:string , type:string|null} , listen:string} $data
     * @return self
     */
    public static function serialize(array $data): PostmanEvent
    {
        return new self(
            PostmanScript::serialize($data['script']),
            $data['listen']
        );
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array{listen:string , script:array}
     */
    public function toArray(): array
    {
        return [
            "listen" => $this->listen,
            'script' => $this->script->toArray(),
        ];
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
