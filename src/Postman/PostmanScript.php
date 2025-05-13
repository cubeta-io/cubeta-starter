<?php

namespace Cubeta\CubetaStarter\Postman;

use Illuminate\Support\Collection;

class PostmanScript implements PostmanObject
{
    /** @var array<string> */
    public array $exec;
    public string $type = "text/javascript";

    /**
     * @param string[] $exec
     * @param string $type
     */
    public function __construct(array $exec, string $type = "text/javascript")
    {
        $this->exec = $exec;
        $this->type = $type;
    }

    public static function grapeTokenExec(): PostmanScript
    {
        return new self([
            "var jsonData = JSON.parse(responseBody);\r",
            "if(jsonData.data.token){pm.collectionVariables.set(\"token\", jsonData.data.token);}",
        ]);
    }

    /**
     * @param array{exec:string , type:string|null} $data
     * @return self
     */
    public static function serialize(array $data): PostmanScript
    {
        return new self($data['exec'], $data['type'] ?? 'text/javascript');
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array{type:string , exec:array{string}}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'exec' => $this->exec,
        ];
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
