<?php

namespace App\Http\Resources\BaseResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as IlluminateAnonymousResourceCollection;

class AnonymousResourceCollection extends IlluminateAnonymousResourceCollection
{
    private array $additions = [];
    private array $meta = [];

    public function extra(array $extraData = []): static
    {
        $this->additions = [...$this->additions, ...$extraData];
        return $this;
    }

    public function meta(array $metaData): static
    {
        $this->meta = array_merge($this->meta, $metaData);
        return $this;
    }

    public function toArray(Request $request)
    {
        return $this->collection->map(function (BaseResource $item) use ($request) {
            $item->extra($this->additions);
            $itemToArray = $item->toArray($request);
            return array_merge($itemToArray, $item->additions);
        })->all();
    }
}
