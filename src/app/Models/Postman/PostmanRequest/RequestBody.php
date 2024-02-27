<?php

namespace Cubeta\CubetaStarter\App\Models\Postman\PostmanRequest;

use Cubeta\CubetaStarter\App\Models\Postman\PostmanObject;
use Illuminate\Support\Collection;

class RequestBody implements PostmanObject
{
    public string $mode = "formdata";

    /** @var FormDataField[] */
    public array $formdata = [];

    /**
     * @param string $mode
     * @param FormDataField[] $formdata
     */
    public function __construct(string $mode, array $formdata)
    {
        $this->mode = $mode;
        $this->formdata = $formdata;
    }

    /**
     * @param array{mode:string|null , formdata:array{key:string|null , value:string|null , type:string|null}} $data
     * @return self
     */
    public static function serialize(array $data)
    {
        return new self(
            $data['mode'] ?? '',
            array_map(fn($field) => FormDataField::serialize($field), $data['formdata'] ?? [])
        );
    }

    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    public function toArray(): array
    {
        $fields = [];

        foreach ($this->formdata as $field) {
            $fields[] = $field->toArray();
        }

        return [
            'mode' => $this->mode,
            "{$this->mode}" => $fields
        ];
    }

    public function toJson(): bool|string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
