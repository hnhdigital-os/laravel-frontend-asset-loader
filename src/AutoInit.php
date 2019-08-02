<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

class AutoInit
{
    public function __construct()
    {
        app('FrontendAsset')->add('vendor/autoinit.js');
    }
}
