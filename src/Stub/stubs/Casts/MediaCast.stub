<?php

namespace App\Casts;

use Exception;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaCast implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    /**
     * Cast the given value.
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_string($value) || Str::isJson($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     * @param array<string, mixed> $attributes
     * @throws Exception
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|null|false
    {
        if (is_null($value)) {
            self::deleteFiles($model->{$key});
            return null;
        }

        $paths = $this->handleFileStoring($value, $model);
        if (is_string($paths)) {
            return json_encode($this->format($paths));
        } elseif (is_array($paths)) {
            foreach ($paths as $key => $item) {
                $paths[$key] = $this->format($item);
            }
            return json_encode($paths, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (!$paths) {
            return null;
        }
        throw new Exception("Invalid Media Value");
    }

    private function format(string $value): array
    {
        $fullPath = storage_path("app/public/" . trim($value, '/'));
        $fileExists = file_exists($fullPath);
        $extension = File::extension($fullPath);
        return [
            'url' => asset("storage/$value"),
            'size' => $fileExists ? round(filesize($fullPath) / 1024) : 0,
            'extension' => $extension,
            'mime_type' => $fileExists ? MimeType::fromExtension($extension) : "unknown",
        ];
    }

    private function handleFileStoring(UploadedFile|array $value, Model $model): array|string|null
    {
        $images = [];

        $isArray = is_array($value);
        /** @var UploadedFile[] $files */
        $files = Arr::wrap($value);

        foreach ($files as $file) {
            $images[] = $this->storeFile($file, $model->getTable());
        }

        return $isArray
            ? $images
            : (count($images) > 0
                ? $images[0]
                : null
            );
    }

    public function storeFile(UploadedFile $file, $dir): string
    {
        $directory = storage_path("app/public/$dir");
        if (!is_dir($directory)) {
            File::makeDirectory(storage_path("app/public/$dir"), 0777, true);
        }
        return $file->store($dir, [
            'disk' => 'public',
        ]);
    }

    private static function deleteFileByUrl(string $url): void
    {
        $path = str_replace(asset('storage/'), '', $url);
        $fullPath = storage_path("app/public/$path");
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public static function deleteFiles(array $media): void
    {
        if (isset($media['url'])) {
            self::deleteFileByUrl($media['url']);
        } else {
            foreach ($media as $file) {
                if (is_array($file) && isset($file['url'])) {
                    self::deleteFileByUrl($file['url']);
                }
            }
        }
    }
}
