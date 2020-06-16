<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

/*
 * Frontend Asset Management
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */

use Arr;
use Html;

class FrontendAsset
{
    /**
     * The domain for assets.
     *
     * @var string
     */
    private $domain = '/';

    /**
     * Secure?
     *
     * @var string
     */
    private $secure = false;

    /**
     * Assets.
     *
     * @var array
     */
    private $assets = [];

    /**
     * Packages.
     *
     * @var array
     */
    private $packages = [];

    /**
     * Meta entries.
     *
     * @var array
     */
    private $meta = [];

    /**
     * Extension mapping.
     *
     * @var array
     */
    private $extension_mapping = [
        'css'  => ['css'],
        'js'   => ['js'],
    ];

    /**
     * Extension mapping.
     *
     * @var array
     */
    public $extension_default_locations = [
        'css'  => 'header',
        'js'   => 'footer',
    ];

    public function __construct()
    {
        $this->assets = collect();
    }

    /**
     * Is CDN activated?
     *
     * @return bool
     */
    public function cdn()
    {
        return config('hnhdigital.assets.cdn', true);
    }

    /**
     * Set the domain.
     *
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = rtrim($domain, '/');

        return $this;
    }

    /**
     * Get the domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Is secure?
     *
     * @return string
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Is secure?
     *
     * @return string
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Identify where to add an asset:.
     *
     * @param string $path
     * @param string $location
     *
     * @return array
     */
    public function parseExtension($path, $location = null)
    {
        $key = null;

        foreach ($this->extension_mapping as $store => $extensions) {
            foreach ($extensions as $ext) {
                if (preg_match("/(\.".$ext."|\/".$ext."\?)$/i", $path)) {
                    $key = $store;
                    break;
                }
            }
        }

        if (is_null($location) && ! is_null($key)) {
            $location = Arr::get($this->extension_default_locations, $key, 'footer');
        }

        return [$key, $location];
    }

    /**
     * Add asset.
     *
     * @param string $path
     * @param string $location
     *
     * @return void
     */
    public function add($path, $location = null, $attributes = [], $priority = null)
    {
        $asset = Asset::createByPath($path, $location, $attributes);

        if (!is_null($priority)) {
            $asset->setPriority($priority);
        }

        $this->storeAsset($asset);

        return $asset;
    }

    /**
     * Store asset.
     *
     * @return self
     */
    public function storeAsset($asset)
    {
        $this->assets->put($asset->getHash(), $asset);

        return $this;
    }

    /**
     * Add asset.
     *
     * @param string|array $path
     * @param string       $location
     *
     * @return void
     */
    public function addFirst($path, $location = null, $attributes = [])
    {
        return $this->add($path, $location, $attributes, 1);
    }

    /**
     * Get asset by type.
     *
     * @param string $type
     * @param string $location
     *
     * @return Collection
     */
    private function getAssetByType($type, $location)
    {
        return $this->assets->filter(function ($asset, $hash) use ($type, $location) {
            return $asset->location === $location && $asset->type === $type;
        })->sortBy(function ($asset, $hash) {
            return $asset->priority;
        });
    }

    /**
     * Add content.
     *
     * @param string $type
     * @param string $content
     * @param string $location
     *
     * @return void
     */
    public function content($type, $content, $location)
    {
        $asset = Asset::createByContent($path, $content, $location);

        $this->assets[$asset->getHash()] = $asset;
    }

    /**
     * Render asset type for location.
     *
     * @param string $type
     * @param string $location
     *
     * @return string
     */
    public function render($type, $location)
    {
        $result = '';

        $assets = $this->getAssetByType($type, $location);

        foreach ($assets as $asset) {
            $render = $asset->render();
            $render = is_null($render) ? '' : $render."\n";
            $result .= $render;
        }

        return $result;
    }

    /**
     * Get the package integrity.
     *
     * @param string $name
     *
     * @return string
     */
    public function packageInfo($name)
    {
        if (config()->has('hnhdigital.assets.packages.'.$name)) {
            return config('hnhdigital.assets.packages.'.$name);
        }

        return [];
    }

    /**
     * Get the package integrity.
     *
     * @param string $name
     *
     * @return string
     */
    public function packageVersion($name, $version = false)
    {
        if (!empty($version)) {
            return $version;
        }

        if (config()->has('hnhdigital.assets.packages.'.$name.'.version')) {
            return config('hnhdigital.assets.packages.'.$name.'.version');

        // Backwards compatibility.
        } elseif (config()->has('hnhdigital.assets.packages.'.$name.'.1')) {
            return config('hnhdigital.assets.packages.'.$name.'.1');
        }

        return false;
    }

    /**
     * Get the package integrity.
     *
     * @param string $name
     *
     * @return string
     */
    public function packageIntegrity($name, $asset = '')
    {
        $integrity = config('hnhdigital.assets.packages.'.$name.'.integrity', []);

        if (is_array($integrity)) {
            if (isset($integrity[$asset])) {
                return $integrity[$asset];
            } else {
                return '';
            }
        }

        return $integrity;
    }

    /**
     * Add a meta attribute.
     *
     * @return void
     */
    public function addMeta($meta, $data = [])
    {
        if (is_string($meta)) {
            $meta = [$meta => $data];
        }

        foreach ($meta as $key => $data) {
            $this->meta[$key] = $data;
        }
    }

    /**
     * Return meta.
     *
     * @return array
     */
    public function meta()
    {
        foreach ($this->meta as $name => $attributes) {
            echo Html::meta()
                ->name(Arr::has($attributes, 'config.noname') ? false : $name)
                ->addAttributes(Arr::except($attributes, ['config']));
            echo "\n";
        }
    }

    /**
     * Load muiltiple packages.
     *
     * @param array $arguments
     *
     * @return void
     */
    public function packages(...$arguments)
    {
        if (!isset($arguments[0])) {
            return;
        }

        $container_list = $arguments[0];

        foreach ($container_list as $container_settings) {
            $this->package($container_settings);
        }
    }

    /**
     * Add a package.
     *
     * @param array $asset_settings
     *
     * @return void
     */
    public function package($settings, $config = [])
    {
        if (is_array($settings)) {
            $asset_name = array_shift($settings);
        } else {
            $asset_name = $settings;
            $settings = [];
        }

        $class_name = false;

        if ($asset_details = config('hnhdigital.assets.packages.'.$asset_name, false)) {
            $class_name = Arr::get($asset_details, 'class', Arr::get($asset_details, 0, false));
        }

        if ($class_name !== false && !isset($this->packages[$class_name]) && class_exists($class_name)) {
            $this->packages[$class_name] = new $class_name(...$settings);
            $this->callPackage($class_name, 'load', $config);
        }
    }

    /**
     * Call package method.
     *
     * @return mixed
     */
    public function callPackage($class_name, $method, ...$args)
    {
        if (!isset($this->packages[$class_name])) {
            return;
        }

        if (!is_callable([$this->packages[$class_name], $method])) {
            return;
        }

        return $this->packages[$class_name]->$method(...$args);
    }

    /**
     * Autoload assets for a given path.
     *
     * @param array  $extensions
     * @param string $path
     *
     * @return void
     */
    public function autoloadAssets($extensions, $path)
    {
        // Force array.
        $extensions = Arr::wrap($extensions);

        // Replace dots with slashes.
        $path = str_replace('.', '/', $path);

        if (substr($path, -1) === '*') {
            return $this->autoloadWildcardAssets($extensions, substr($path, 0, -1));
        }

        // Go through each file extension folder.
        foreach ($extensions as $extension) {
            $file_name = $path.'.'.$extension;

            $local_file_path = dirname(resource_path().'/views/'.$file_name);
            $local_file_path .= '/'.$extension.'/'.basename($file_name);

            $full_path = '';

            // Adjust for local environment.
            if (app()->environment() === 'local') {
                if (file_exists($local_file_path)) {
                    $full_path = $local_file_path;
                } else {
                    $full_path = public_path().'/assets/'.$file_name;
                }
            }

            $this->loadAsset($file_name, $extension, $full_path);
        }
    }

    /**
     * Autoload assets for a given file path.
     *
     * @param array  $extensions
     * @param string $file
     *
     * @return void
     */
    public function autoloadWildcardAssets($extensions, $path)
    {
        // Force array.
        $extensions = Arr::wrap($extensions);

        // Replace dots with slashes.
        $path = str_replace('.', '/', $file);

        $root_path = dirname($path);
        $filename = basename($path);

        foreach ($extensions as $extension) {
            $extension_dir = $root_path.'/'.$extension.'/'.$filename;
            $scanned_paths = scandir(resource_path().'/views/'.$extension_dir);

            foreach ($scanned_paths as $scanned_file) {
                if ($scanned_file == '.' || $scanned_file == '..') {
                    continue;
                }

                $full_path = resource_path().'/views/'.$extension_dir.'/'.$scanned_file;
                $file_name = $extension_dir.'/'.$scanned_file;

                $this->loadAsset($file_name, $extension, $full_path);
            }
        }
    }

    /**
     * Load an asset.
     *
     * @param string $file_name
     * @param string $extension
     * @param string $full_path
     *
     * @return void
     */
    public function loadAsset($file_name, $extension, $full_path = '')
    {
        // Load asset as script/link.
        // File needs to be in the manifest.
        if (!config('hnhdigital.assets.inline', false)) {

            // File is not in the manifest.
            if (!Arr::has(config(config('hnhdigital.assets.manifest-revisions'), []), $file_name)) {
                return;
            }

            $this->add($file_name);

            return;
        }

        // Path is empty or the path does not exist.
        if (!empty($full_path) && !file_exists($full_path)) {
            return;
        }

        // Add the file inline.
        $this->add($full_path, 'footer-inline');
    }

    /**
     * Get the URL for given path.
     *
     * @param string $path
     *
     * @return return string
     */
    public function url($path)
    {
        // Detect path points to external url.
        if (stripos($path, '://') !== false) {
            return $path;
        }

        if (Arr::has(config(config('hnhdigital.assets.manifest-revisions'), []), $path)) {
            if (config('hnhdigital.assets.source', 'build') === 'build') {
                return '/build/'.Arr::get(config(config('hnhdigital.assets.manifest-revisions'), []), $path);
            }

            return '/'.config('hnhdigital.assets.source').'/'.$path;
        }

        if (file_exists(public_path().'/'.$path)) {
            return $path;
        }

        if (file_exists(public_path().'/assets/'.$path)) {
            return '/assets/'.$path;
        }

        return '';
    }

    /**
     * Enforce HTTP2.
     *
     * @return void
     */
    public function http2()
    {
        if (!config('hnhdigital.assets.http2', false)) {
            return;
        }

        foreach ($this->assets as $asset) {
            $asset->http2();
        }
    }

    /**
     * Output header html.
     *
     * @return string
     */
    public function header()
    {
        $output = '';
        $output .= $this->meta();
        $output .= $this->render('css', 'header');
        $output .= $this->render('css', 'inline');
        $output .= $this->render('js', 'header');
        $output .= $this->render('js', 'header-inline');

        return $output;
    }

    /**
     * Output footer html.
     *
     * @return string
     */
    public function footer()
    {
        $output = '';
        $output .= $this->render('css', 'footer');
        $output .= $this->render('css', 'footer-inline');
        $output .= $this->render('js', 'footer');
        $output .= $this->render('js', 'footer-inline');
        $output .= $this->render('js', 'ready');

        return $output;
    }
}
