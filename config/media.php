<?php

$filesystemDisk = env('FILESYSTEM_DISK', 'local');
$sharedMediaDisk = env('MEDIA_DISK');

return [

    /*
    |--------------------------------------------------------------------------
    | Media Disks
    |--------------------------------------------------------------------------
    |
    | Public and private media use independently configurable disks. They may
    | share a cloud disk whose access policy respects the path prefixes below,
    | but private media must never use Laravel's public local disk.
    |
     */

    'disks' => [
        'public' => env(
            'MEDIA_PUBLIC_DISK',
            $sharedMediaDisk ?? ($filesystemDisk === 'local' ? 'public' : $filesystemDisk),
        ),
        'private' => env(
            'MEDIA_PRIVATE_DISK',
            $sharedMediaDisk ?? (in_array($filesystemDisk, ['local', 'public'], true) ? 'local' : $filesystemDisk),
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary URL Lifetime
    |--------------------------------------------------------------------------
    |
    | Minutes a presigned URL for a private file stays valid. A presigned URL
    | outlives the authorization check that produced it, so this is the window
    | in which a revoked user could still fetch a file they once had access to.
    | Keep it short.
    |
     */

    'temporary_url_ttl' => (int) env('MEDIA_TEMPORARY_URL_TTL', 5),

    /*
    |--------------------------------------------------------------------------
    | Visibility Prefixes
    |--------------------------------------------------------------------------
    |
    | Every stored file lives under one of these, making its visibility
    | derivable from its path alone. The bucket policy grants anonymous reads
    | on the public prefix only
    |
     */

    'prefixes' => [
        'public'  => 'public',
        'private' => 'private',
    ],
];
