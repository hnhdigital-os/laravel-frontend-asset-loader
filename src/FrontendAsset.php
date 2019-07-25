<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

use Roumen\Asset\Asset as RoumenAsset;
use Html;

class FrontendAsset
{
    private static $containers = [];

    /**
     * Meta entries.
     *
     * @var array
     */
    private static $meta = [];

    /**
     * The domain for assets.
     *
     * @var string
     */
    protected static $domain;

    /**
     * Set the domain.
     *
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        self::$domain = rtrim($domain, '/');

        return $this;
    }

    /**
     * Get the domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return self::$domain;
    }

    /**
     * Get the version of the given resource.
     *
     * @return string
     */
    public function version($name, $version = false)
    {
        return (empty($version)) ? config('hnhdigital.assets.packages.'.$name.'.1') : $version;
    }

    /**
     * Track loaded inline files.
     *
     * @var array
     */
    private $loaded_inline = [];

    /**
     * Add the asset using our version of the exlixer loader.
     *
     * @param string $file
     * @param string $params
     * @param bool   $onUnknownExtension
     *
     * @return void
     */
    public function add($file, $params = 'footer', $onUnknownExtension = false)
    {
        RoumenAsset::add($this->elixir($file), $params, $onUnknownExtension);
    }

    /**
     * Add raw script to page.
     *
     * @param string $style
     * @param string $params
     *
     * @return void
     */
    public function addScript($script, $params = 'footer')
    {
        RoumenAsset::addScript($script, $params);
    }

    /**
     * Reverse the order of scripts.
     *
     * @param string $params
     *
     * @return void
     */
    public function reverseStylesOrder($params = 'footer')
    {
        if (isset(RoumenAsset::$scripts[$params])) {
            RoumenAsset::$scripts[$params] = array_reverse(RoumenAsset::$scripts[$params], true);
        }
    }

    /**
     * Add raw styling to page.
     *
     * @param string $style
     * @param string $params
     *
     * @return void
     */
    public function addStyle($style, $params = 'header')
    {
        RoumenAsset::addStyle($style, $params);
    }

    /**
     * Add the asset first using our version of the exliser loader.
     *
     * @param string $file
     * @param string $params
     * @param bool   $onUnknownExtension
     *
     * @return return string
     */
    public function addFirst($file, $params = 'footer', $onUnknownExtension = false)
    {
        RoumenAsset::addFirst($this->elixir($file), $params, $onUnknownExtension);
    }

    /**
     * Add new asset after another asset in its array.
     *
     * @param string       $file1
     * @param string       $file2
     * @param string|array $params
     * @param bool         $onUnknownExtension
     *
     * @return void
     */
    public function addAfter($file1, $file2, $params = 'footer', $onUnknownExtension = false)
    {
        RoumenAsset::addAfter($this->elixir($file1), $this->elixir($file2), $params, $onUnknownExtension);
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
            self::$meta[$key] = $data;
        }
    }

    /**
     * Return meta.
     *
     * @return array
     */
    public function meta()
    {
        foreach(self::$meta as $name => $attributes) {
            echo Html::meta()
                ->name(array_has($attributes, 'config.noname') ? false : $name)
                ->addAttributes(array_except($attributes, ['config']));
            echo "\n";
        }
    }

    /**
     * Return CSS.
     *
     * @param array $arguments
     *
     * @return void
     */
    public function css(...$arguments)
    {
        return RoumenAsset::css(...$arguments);
    }

    /**
     * Return LESS.
     *
     * @param array $arguments
     *
     * @return string
     */
    public function less(...$arguments)
    {
        return RoumenAsset::less(...$arguments);
    }

    /**
     * Return styles.
     *
     * @param array $arguments
     *
     * @return string
     */
    public function styles(...$arguments)
    {
        return RoumenAsset::styles(...$arguments);
    }

    /**
     * Return javascript.
     *
     * @param array $arguments
     *
     * @return string
     */
    public function js(...$arguments)
    {
        return RoumenAsset::js(...$arguments);
    }

    /**
     * Return scripts.
     *
     * @param array $arguments
     *
     * @return string
     */
    public function scripts(...$arguments)
    {
        return RoumenAsset::scripts(...$arguments);
    }

    /**
     * Add a package.
     *
     * @param array $container_settings
     * @param array $config
     *
     * @return void
     */
    public static function package($container_settings, $config = [])
    {
        self::loadContainer($container_settings, $config);
    }

    /**
     * Add new asset after another asset in its array.
     *
     * @param array $container_settings
     *
     * @return void
     */
    public static function container($container_settings, $config = [])
    {
        self::loadContainer($container_settings, $config);
    }

    /**
     * Load an assets container (it will load the individual files).
     *
     * @param array $arguments
     *
     * @return void
     */
    public function containers(...$arguments)
    {
        if (isset($arguments[0])) {
            $container_list = $arguments[0];
            foreach ($container_list as $container_settings) {
                $this->loadContainer($container_settings);
            }
        }
    }

    /**
     * Load an assets container (it will load the individual files).
     *
     * @param array $asset_settings
     *
     * @return void
     */
    private static function loadContainer($class_settings, $config = [])
    {
        if (is_array($class_settings)) {
            $asset_name = array_shift($class_settings);
        } else {
            $asset_name = $class_settings;
            $class_settings = [];
        }

        $class_name = false;

        if ($asset_details = config('hnhdigital.assets.packages.'.$asset_name, false)) {
            $class_name = array_get($asset_details, 0, false);
        }

        if ($class_name !== false && !isset(self::$containers[$class_name]) && class_exists($class_name)) {
            self::$containers[$class_name] = new $class_name(...$class_settings);
        }

        if (!empty($config) && method_exists(self::$containers[$class_name], 'config')) {
            if (!is_array($config)) {
                $config = [$config];
            }
            self::$containers[$class_name]->config(...$config);
        }
    }

    /**
     * Load local files for a given controller.
     *
     * @param array  $file_extensions
     * @param string $file
     *
     * @return void
     */
    public function controller($file_extensions, $file)
    {
        // Only look in a single file extension folder.
        if (!is_array($file_extensions)) {
            $file_extensions = [$file_extensions];
        }

        // Replace dots with slashes.
        $file = str_replace('.', '/', $file);

        if (substr($file, -1) === '*') {
            $folder = dirname(substr($file, 0, -1));
            $base_folder = basename(substr($file, 0, -1));

            foreach ($file_extensions as $extension) {
                $extension_folder = $folder.'/'.$extension.'/'.$base_folder;
                $folder_contents = scandir(resource_path().'/views/'.$extension_folder);

                foreach ($folder_contents as $folder_file) {
                    if ($folder_file == '.' || $folder_file == '..') {
                        continue;
                    }

                    $full_path = resource_path().'/views/'.$extension_folder.'/'.$folder_file;
                    $file_name = $extension_folder.'/'.$folder_file;

                    $this->loadFile($file_name, $extension, $full_path);
                }
            }

            return;
        }

        // Go through each file extension folder.
        foreach ($file_extensions as $extension) {
            $file_name = $file.'.'.$extension;

            $local_file_path = dirname(resource_path().'/views/'.$file_name);
            $local_file_path .= '/'.$extension.'/'.basename($file_name);

            $full_path = '';

            if (app()->environment() == 'local') {
                if (file_exists($local_file_path)) {
                    $full_path = $local_file_path;
                } else {
                    $full_path = public_path().'/assets/'.$file_name;
                }
            }

            $this->loadFile($file_name, $extension, $full_path);
        }
    }

    /**
     * Load a file.
     *
     * @param string $file_name
     * @param string $extension
     * @param string $full_path
     *
     * @return void
     */
    public function loadFile($file_name, $extension, $full_path = '')
    {
        if (array_has(config('rev-manifest', []), $file_name) || (!empty($full_path) && file_exists($full_path))) {
            if (config('hnhdigital.assets.inline', false)) {
                if (!isset($this->loaded_inline[$full_path])) {
                    $contents = file_get_contents($full_path);
                    $contents = '/* '.$file_name." */ \n\n".$contents;
                    if ($extension == 'js') {
                        $this->addScript($contents, 'inline');
                    } else {
                        $this->addStyle($contents);
                    }
                    $this->loaded_inline[$full_path] = true;
                }
            } else {
                $this->add($file_name, 'ready');
            }
        }
    }

    /**
     * Override standard elixir to return standard url if
     * the exception is made (eg the file isn't versioned).
     *
     * @param string $file
     *
     * @return return string
     */
    public function elixir($file)
    {
        if (substr($file, 0, 4) === 'http') {
            return $file;
        }

        try {
            if (config('hnhdigital.assets.source', 'build') === 'build') {
                $elixir_path = elixir($file);

                return $elixir_path;
            }

            return '/'.config('hnhdigital.assets.source').'/'.$file;
        } catch (\InvalidArgumentException $e) {
            if (file_exists(public_path().'/'.$file)) {
                return $file;
            } elseif (file_exists(public_path().'/assets/'.$file)) {
                return '/assets/'.$file;
            }
        }

        return '';
    }

    /**
     * Enforce HTTP2
     *
     * @return void
     */
    public static function http2()
    {
        foreach (RoumenAsset::$css as $file) {
            header('Link: <'.$file.'>; rel=preload; as=style;', false);
        }
        foreach (RoumenAsset::$js as $section) {
            foreach ($section as $file) {
                header('Link: <'.$file.'>; rel=preload; as=script;', false);
            }
        }
    }

    /**
     * Output header html.
     *
     * @return string
     */
    public function head()
    {
        $output = '';
        $output .= $this->meta();
        $output .= $this->css();
        $output .= $this->less();
        $output .= $this->styles('header');
        $output .= $this->js('header');
        $output .= $this->scripts('header');

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
        $output .= $this->js();
        $output .= $this->scripts('footer');
        $output .= $this->scripts('inline');
        $output .= $this->js('ready');
        $output .= $this->scripts('ready');

        return $output;
    }
}
