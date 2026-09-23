<?php

namespace App\Casts;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use App\Serializers\SerializedMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MediaCast implements CastsAttributes
{
    private readonly bool $isSingle;

    private readonly bool $private;

    /** @noinspection PhpUnused */
    public bool $withoutObjectCaching = true;

    /**
     * @param string $privateOrPublic "public" or "private"
     * @param string $structureType   "array" or "single"
     */
    public function __construct(string $privateOrPublic = 'public', string $structureType = 'single')
    {
        $this->private = $privateOrPublic === 'private';
        $this->isSingle = $structureType === 'single';
    }

    /**
     * Get all file paths currently saved in this column record to act as an ownership whitelist.
     *
     * @return array<int, string>
     */
    private function getCurrentlyOwnedPaths(Model $model, string $key): array
    {
        $rawOriginal = $model->getRawOriginal($key);
        if (empty($rawOriginal)) {
            return [];
        }

        $decoded = json_decode($rawOriginal, true);
        if (! is_array($decoded)) {
            return [];
        }

        $items = SerializedMedia::isMediaArray($decoded) ? [$decoded] : $decoded;

        return collect($items)
            ->filter(fn ($item) => SerializedMedia::isMediaArray($item))
            ->map(fn ($item) => (new SerializedMedia($item, $model->getTable(), $this->private))->path())
            ->all();
    }

    public static function array(bool $private = false): string
    {
        return static::class
            .':'
            .($private ? 'private' : 'public')
            .',array';
    }

    /**
     * @throws Exception
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): SerializedMedia|array|null
    {
        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        if (! is_array($data)) {
            throw new Exception("Invalid stored data in the media column [$key], table : {$model->getTable()}");
        }

        if ($this->isSingle) {
            if (SerializedMedia::isMediaArray($data)) {
                return new SerializedMedia($data, $model->getTable(), $this->private);
            }

            /** @var array|null $first */
            $first = collect($data)->first(fn ($item) => SerializedMedia::isMediaArray($item));

            return $first ? new SerializedMedia($first, $model->getTable(), $this->private) : null;
        }

        if (SerializedMedia::isMediaArray($data)) {
            return [new SerializedMedia($data, $model->getTable(), $this->private)];
        }

        return array_values(
            array_map(
                fn (array $item) => new SerializedMedia($item, $model->getTable(), $this->private),
                array_filter($data, fn ($item) => SerializedMedia::isMediaArray($item)),
            ),
        );
    }

    /**
     * @throws Exception
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $allowedPaths = $this->getCurrentlyOwnedPaths($model, $key);

        /** @var Collection<int,SerializedMedia> $newMediaCollection */
        $newMediaCollection = collect();

        $securePushArray = function (array $item) use ($newMediaCollection, $model, $allowedPaths) {
            if (SerializedMedia::isMediaArray($item)) {
                $media = new SerializedMedia($item, $model->getTable(), $this->private);

                if (in_array($media->path(), $allowedPaths, true)) {
                    $newMediaCollection->push($media);
                }
            }
        };

        if ($value instanceof SerializedMedia) {
            $newMediaCollection->push($value);
        } elseif ($value instanceof UploadedFile) {
            $newMediaCollection->push(new SerializedMedia($value, $model->getTable(), $this->private));
        } elseif (is_array($value)) {
            if (SerializedMedia::isMediaArray($value)) {
                $securePushArray($value);
            } else {
                foreach ($value as $item) {
                    if ($item instanceof SerializedMedia) {
                        $newMediaCollection->push($item);
                    } elseif ($item instanceof UploadedFile) {
                        $newMediaCollection->push(new SerializedMedia($item, $model->getTable(), $this->private));
                    } elseif (is_array($item)) {
                        $securePushArray($item);
                    }
                }
            }
        } elseif (Str::isJson($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                if (SerializedMedia::isMediaArray($decoded)) {
                    $securePushArray($decoded);
                } else {
                    foreach ($decoded as $item) {
                        if (is_array($item)) {
                            $securePushArray($item);
                        }
                    }
                }
            }
        } else {
            throw new Exception('Unhandled Media Type Within Media Cast');
        }

        if ($this->isSingle) {
            $targetMedia = $newMediaCollection->first();

            return $targetMedia?->toJson();
        }

        $arrayData = $newMediaCollection->map(fn (SerializedMedia $media) => $media->toArray())->all();

        return json_encode($arrayData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function single(bool $private = false): string
    {
        return static::class
            .':'
            .($private ? 'private' : 'public')
            .',single';
    }
}