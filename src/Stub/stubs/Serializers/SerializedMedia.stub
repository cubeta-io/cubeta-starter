<?php

namespace App\Serializers;

use Exception;
use Stringable;
use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Filesystem\FilesystemAdapter;

use function Illuminate\Filesystem\join_paths;

class SerializedMedia implements Arrayable, Jsonable, JsonSerializable, Stringable
{
    private string $storagePath;

    public readonly string $dir;

    public readonly string $extension;

    public readonly string $mimeType;

    public readonly bool $private;

    public readonly float $size;

    /**
     * @param UploadedFile|array{storage_path:string,extension:string,mime_type:string,size:int} $file
     */
    public function __construct(
        UploadedFile|array $file,
        ?string $dir = null,
        bool $private = false,
    ) {
        $this->dir = $dir ?? '';

        if (self::isMediaArray($file)) {
            $this->storagePath = $file['storage_path'];
            $this->private = self::visibilityFromPath($this->storagePath) ?? $private;
            $this->extension = $file['extension'];
            $this->mimeType = $file['mime_type'];
            $this->size = (float) $file['size'];
        } else {
            $this->private = $private;
            $this->storagePath = $this->storeFile($file);
            $this->size = round(($file->getSize() ?? 0) / 1024);
            $this->extension = $this->guessExtension() ?? '';
            $this->mimeType = $file->getMimeType() ?? 'unknown';
        }
    }

    private function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(self::diskName());

        return $disk;
    }

    private function guessExtension(): ?string
    {
        $extension = pathinfo($this->storagePath, PATHINFO_EXTENSION);

        return $extension !== '' ? $extension : null;
    }

    private function prefix(): string
    {
        return (string) config($this->private ? 'media.prefixes.private' : 'media.prefixes.public');
    }

    private function storeFile(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Normalize aggressively
        $filenameOnly = str($originalName)
            ->lower()
            ->ascii() // removes Unicode
            ->replaceMatches('/[^a-z0-9\-_]/', '-') // force safe chars only
            ->replaceMatches('/-+/', '-') // collapse repeats
            ->trim('-')
            ->limit(80, '') // prevent filesystem/path issues
            ->toString();

        if (empty($filenameOnly)) {
            $filenameOnly = Str::random();
        }

        $extension = $file->extension();

        if (empty($extension)) {
            $extension = $file->getClientOriginalExtension();
        }

        $extension = preg_replace('/[^a-z0-9]/', '', strtolower($extension));

        $finalFileName = $filenameOnly.'.'.$extension;

        $finalFileName = ltrim($finalFileName, '.');

        return $file->storeAs(
            join_paths($this->prefix(), $this->dir, Str::uuid()->toString()),
            $finalFileName,
            [
                'disk' => self::diskName(),
                'visibility' => $this->private ? 'private' : 'public',
            ],
        );
    }

    private static function visibilityFromPath(string $path): ?bool
    {
        $prefix = Str::before(ltrim($path, '/'), '/');

        return match ($prefix) {
            (string) config('media.prefixes.private') => true,
            (string) config('media.prefixes.public') => false,
            default => null,
        };
    }

    /**
     * The database shape — never the API shape, or a `(string)` cast would
     * write a URL into the column.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * @throws Exception
     */
    public function contents(): string
    {
        $contents = $this->disk()->get($this->path());

        if (is_null($contents)) {
            throw new Exception("Cannot read the media file at [$this->storagePath]");
        }

        return $contents;
    }

    /**
     * @throws Exception
     */
    public function copyTo(string $dir, ?bool $private = null): self
    {
        return new self($this->getUploadedFile(), $dir, $private ?? $this->private);
    }

    public function delete(): bool
    {
        if (empty($this->storagePath)) {
            return false;
        }

        $directory = dirname($this->storagePath);

        if ($directory === '' || $directory === '.' || $directory === DIRECTORY_SEPARATOR) {
            return $this->disk()->delete($this->storagePath);
        }

        return $this->disk()->deleteDirectory($directory);
    }

    /**
     * The disk every media file is read from and written to.
     */
    public static function diskName(): string
    {
        return (string) config('media.disk');
    }

    /**
     * Hits the disk. to check if the file exists
     */
    public function exists(): bool
    {
        return ! empty($this->storagePath) && $this->disk()->exists($this->storagePath);
    }

    /**
     * Store bytes we produced ourselves rather than a file somebody uploaded.
     */
    public static function fromContents(
        string $contents,
        string $name,
        string $mimeType,
        ?string $dir = null,
        bool $private = false,
    ): self {
        $path = join_paths(
            (string) config($private ? 'media.prefixes.private' : 'media.prefixes.public'),
            $dir ?? '',
            Str::uuid()->toString(),
            $name,
        );

        Storage::disk(self::diskName())->put(
            $path,
            $contents,
            ['visibility' => $private ? 'private' : 'public'],
        );

        return new self([
            'storage_path' => $path,
            'extension' => pathinfo($name, PATHINFO_EXTENSION),
            'mime_type' => $mimeType,
            'size' => round(strlen($contents) / 1024),
        ], $dir, $private);
    }

    /**
     * @throws Exception
     */
    public function getUploadedFile(): UploadedFile
    {
        $stream = $this->disk()->readStream($this->storagePath);

        if (! is_resource($stream)) {
            throw new Exception("Cannot read the media file at [$this->storagePath]");
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'media-');
        $target = fopen($temporaryPath, 'wb');
        stream_copy_to_stream($stream, $target);
        fclose($target);
        fclose($stream);

        return new UploadedFile(
            $temporaryPath,
            $this->name().'.'.$this->extension,
            ! empty($this->mimeType) && $this->mimeType != 'unknown' ? $this->mimeType : null,
            null,
            true,
        );
    }

    public static function isMediaArray(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        return isset($value['storage_path'])
            && isset($value['extension'])
            && isset($value['mime_type'])
            && isset($value['size']);
    }

    /**
     * The API shape.
     */
    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url(),
            'size' => $this->size,
            'extension' => $this->extension,
            'mime_type' => $this->mimeType,
            'storage_path' => $this->storagePath,
        ];
    }

    public function name(): string
    {
        return pathinfo($this->storagePath, PATHINFO_FILENAME);
    }

    /**
     * The disk-relative path
     *
     * @throws Exception
     */
    public function path(): string
    {
        if (empty($this->storagePath)) {
            throw new Exception('Undefined Storage Path For Media, Cannot Get Its Path');
        }

        return $this->storagePath;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'storage_path' => $this->storagePath,
            'size' => $this->size,
            'extension' => $this->extension,
            'mime_type' => $this->mimeType,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
    * A private object is not anonymously readable, so it has to be signed.
    * Disks that cannot sign (a plain local disk) fall back to the direct
    * URL rather than throwing.
    */
    public function url(): string
    {
        $disk = $this->disk();

        // A private object is not anonymously readable, so it has to be signed.
        // Disks that cannot sign (a plain local disk) fall back to the direct
        // URL rather than throwing.
        if ($this->private && $disk->providesTemporaryUrls()) {
            return $disk->temporaryUrl(
                $this->storagePath,
                now()->addMinutes((int) config('media.temporary_url_ttl')),
            );
        }

        return $disk->url($this->storagePath);
    }
}