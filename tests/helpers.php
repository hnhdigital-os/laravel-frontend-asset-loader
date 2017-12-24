<?php

$env = [
    'APP_ASSET_SOURCE' => 'asset',
];

$rev = [
    'style.css' => '/build/style.123456.css',
    'extra.js'  => '/build/extra.123456.js',
];

function config()
{
}

function elixir($file)
{
    global $rev;
    if (isset($rev[$file])) {
        return $rev[$file];
    }

    throw new \InvalidArgumentException();
}

function env($name, $default = '')
{
    global $env;

    return isset($env[$name]) ? $env[$name] : $default;
}

function public_path()
{
    return __DIR__.'/temp';
}
