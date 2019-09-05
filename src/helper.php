<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

function hookAddClassHtmlTag($class_name)
{
    if (strpos($class_name, 'init-') !== false) {
        $container = Str::studly(str_replace(['init-', '-'], ['', '_'], $class_name));
        app('FrontendAsset')->package($container);
    }

    if (strpos($class_name, 'config-') !== false) {
        $class_name_array = explode('_', $class_name);
        $container = Str::studly(str_replace(['config-', '-'], ['', '_'], Arr::get($class_name_array, 0)));
        $config = explode('-', Arr::get($class_name_array, 1, ''));
        app('FrontendAsset')->package($container, $config);
    }
}

// Override the default asset function.
if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool   $secure
     *
     * @return string
     */
    function asset($path, $secure = null)
    {
        if (!empty(app('FrontendAsset')->getDomain())) {
            return app('FrontendAsset')->getDomain().'/'.trim($path, '/');
        }

        return app('url')->asset($path, $secure);
    }
}
