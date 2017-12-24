<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

use FrontendAsset;

class AutoInit
{
    public function __construct()
    {
        FrontendAsset::add('vendor/autoinit.js');
    }
}
