<?php

return [
    'http2'              => env('APP_ASSET_HTTP2', true),
    'cdn'                => env('APP_ASSET_CDN', true),
    'inline'             => env('APP_ASSET_INLINE', false),
    'source'             => env('APP_ASSET_SOURCE', 'build'),
    'manifest-revisions' => env('APP_ASSET_MANIFEST_REVISIONS', 'manifest-rev'),
    'manifest-integrity' => env('APP_ASSET_MANIFEST_INTEGRITY', 'manifest-integrity'),
];
