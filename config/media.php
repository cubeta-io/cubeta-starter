<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk every media column reads from and writes to. Public
    | and private media share one disk and are separated by the path prefixes
    | below, so this is a single value rather than a pair.
    |
     */

    'disk' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 'local')),

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

    'temporary_url_ttl' => (int)env('MEDIA_TEMPORARY_URL_TTL', 5),

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
        'public' => 'public',
        'private' => 'private',
    ],
];
