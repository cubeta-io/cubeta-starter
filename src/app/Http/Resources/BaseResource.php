<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use JsonSerializable;

class BaseResource extends JsonResource
{
    protected bool $detailed = false;
    protected ?array $extra;

    public static function collectionWithDetail($data, array $extra = null): Collection
    {
        return collect()->wrap($data)->map(fn($item) => self::makeWithDetail($item, $extra))->values();
    }

    public static function makeWithDetail($data, array $extra = null): BaseResource
    {
        return self::make($data)->withDetail()->withExtra($extra);
    }

    public function withExtra($extra): static
    {
        $this->extra = $extra;
        return $this;
    }

    public function withDetail(): static
    {
        $this->detailed = true;
        return $this;
    }

    public static function collectionWithExtra($data, array $extra = null): Collection
    {
        return collect()->wrap($data)->map(fn($item) => self::makeWithExtra($item, $extra))->values();
    }

    public static function makeWithExtra($data, array $extra = null): BaseResource
    {
        return self::make($data)->withExtra($extra);
    }

    /**
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->resource->toArray();
    }
}
