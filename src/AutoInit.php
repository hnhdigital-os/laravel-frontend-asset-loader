<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

use Resource;

class AutoInit
{
    public function __construct()
    {
        Resource::add('vendor/autoinit.js');
    }
}
