<?php

namespace Cubeta\CubetaStarter\app\Models\Postman\PostmanRequest;

use Cubeta\CubetaStarter\app\Models\Postman\PostmanObject;
use Illuminate\Support\Collection;

/**
 * @implements PostmanObject<FormDataField>
 */
class FormDataField implements PostmanObject
{
    const TEXT = 'text';
    const FILE = 'file';

    public string $key;
    public string $value;
    public ?string $description = null;
    public string $type = 'text';

    /**
     * @param string $key
     * @param string $value
     * @param string|null $description
     * @param string $type
     */
    public function __construct(string $key, string $value, ?string $description = null, string $type = 'text')
    {
        $this->key = $key;
        $this->value = $value;
        $this->description = $description;
        $this->type = $type;
    }

    /**
     * @param array{key:string|null , value:string|null , type:string|null} $data
     * @return self
     */
    public static function serialize(array $data): FormDataField
    {
        return new self(
            $data['key'] ?? '',
            $data['value'] ?? '',
            $data['description'] ?? '',
            $data['type'] ?? self::TEXT
        );
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array{key:string , value:string , type:string , description:string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description
        ];
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
