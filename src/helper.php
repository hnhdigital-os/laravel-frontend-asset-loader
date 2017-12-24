<?php

use HnhDigital\LaravelFrontendAssetLoader\Resource;

function hookAddClassHtmlTag($class_name)
{
    if (strpos($class_name, 'init-') !== false) {
        $container = studly_case(str_replace(['init-', '-'], ['', '_'], $class_name));
        Resource::container($container);
    }

    if (strpos($class_name, 'config-') !== false) {
        $class_name_array = explode('_', $class_name);
        $container = studly_case(str_replace(['config-', '-'], ['', '_'], array_get($class_name_array, 0)));
        $config = explode('-', array_get($class_name_array, 1, ''));
        Resource::container($container, $config);
    }
}
