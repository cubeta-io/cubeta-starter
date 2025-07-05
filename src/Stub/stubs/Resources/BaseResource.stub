<?php

namespace App\Http\Resources\BaseResource;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BaseResource\AnonymousResourceCollection;

class BaseResource extends JsonResource
{
    public array $additions = [];

    protected bool $detailed = false;

    protected mixed $data;

    public function detailed(): static
    {
        $this->detailed = true;
        return $this;
    }

    public function extra(array $extraData = []): static
    {
        $this->additions = [...$this->additions, ...$extraData];
        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), $this->additions);
    }

    /**
     * @param $resource
     * @return AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        return parent::collection($resource);
    }

    protected static function newCollection($resource): AnonymousResourceCollection
    {
        return new AnonymousResourceCollection($resource, static::class);
    }
}
