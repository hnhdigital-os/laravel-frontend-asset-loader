<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

use Arr;

/**
 * Base class.
 */
abstract class PackageAbstract
{
    /**
     * Package name.
     *
     * @var string
     */
    protected $package_name;

    /**
     * Version.
     *
     * @var string
     */
    protected $version;

    /**
     * Default constructor.
     *
     * @param bool $version
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct($version = false)
    {
        $this->package_name = class_basename(static::class);
        $this->version = $this->lookupVersion($version);
    }

    /**
     * Check if CDN is enabled.
     *
     * @return bool
     */
    public function isCdn()
    {
        return app('FrontendAsset')->cdn();
    }

    /**
     * Load packages.
     *
     * @return void
     */
    public function load($config)
    {
        $this->callMethod('before');

        // If the package provides cdn/local methods.
        if ($this->isCdn()) {
            $this->callMethod('cdn');
        } elseif (!$this->isCdn()) {
            $this->callMethod('local');
        }

        $this->callMethod('after');

        if (!empty($config)) {
            $this->callMethod('local', Arr::wrap($config));
        }
    }

    /**
     * Call method.
     *
     * @return mixed
     */
    public function callMethod($method, ...$args)
    {
        if (!is_callable([$this, $method])) {
            return;
        }

        return $this->$method(...$args);
    }

    /**
     * Lookup verison.
     *
     * @return string
     */
    public function lookupVersion($version)
    {
        return app('FrontendAsset')->version($this->name(), $version);
    }

    /**
     * Get package name.
     *
     * @return string
     */
    public function name()
    {
        return $this->package_name;
    }

    /**
     * Get package version.
     *
     * @return string
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * Get package info.
     *
     * @return mixed
     */
    public function info(...$args)
    {
        return Arr::get(app('FrontendAsset')->packageInfo($this->name()), ...$args);
    }

    /**
     * Get package integrity.
     *
     * @return string
     */
    public function integrity()
    {
        return app('FrontendAsset')->packageIntegrity($this->name());
    }

    /**
     * Load package.
     *
     * @param string $package
     *
     * @return void
     */
    public function package($package)
    {
        app('FrontendAsset')->package($package);
    }

    /**
     * Add file.
     *
     * @param string $path
     *
     * @return void
     */
    public function add(...$args)
    {
        app('FrontendAsset')->add(...$args);
    }

    /**
     * Add content.
     *
     * @param string $path
     *
     * @return void
     */
    public function content(...$args)
    {
        app('FrontendAsset')->content(...$args);
    }

    /**
     * Add file first.
     *
     * @param string $path
     *
     * @return void
     */
    public function addFirst(...$args)
    {
        app('FrontendAsset')->addFirst(...$args);
    }
}
