<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

use Illuminate\Support\Arr;

/**
 * Asset.
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */
class Asset
{
    private $path;
    private $content;
    private $location;
    private $type;
    private $hash;
    private $attributes = [];

    /**
     * Priority of asset.
     *
     * @var int
     */
    private $priority = 100;

    /**
     * Create asset by path factory.
     *
     * @param string $path
     * @param string $location
     * @param array  $attributes
     *
     * @return string
     */
    public static function createByPath($path, $location, $attributes = [])
    {
        $asset = (new self())
            ->setPath($path);

        list($type, $location) = app('FrontendAsset')->parseExtension($asset->getPath(), $location);

        $asset->setType($type)
            ->setLocation($location)
            ->setAttributes($attributes);

        return $asset;
    }

    /**
     * Create asset by path factory.
     *
     * @param string $path
     * @param string $location
     *
     * @return string
     */
    public static function createByContent($type, $content, $location)
    {
        $asset = (new self())
            ->setType($type)
            ->setContent($content)
            ->setLocation($location);

        return $asset;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath($path)
    {
        if (stripos($path, base_path()) === false) {
            $path = app('FrontendAsset')->url($path);
        }

        $this->path = $path;

        $this->hash = hash('sha256', $this->path);

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the type.
     *
     * @param string $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        if (is_null($this->location)) {
            $this->setLocation(Arr::get(app('FrontendAsset')->extension_default_locations, $this->type, 'footer'));
        }

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the location.
     *
     * @param string $location
     *
     * @return self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the content.
     *
     * @param string $content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        $this->hash = hash('sha256', $this->content);

        return $this;
    }

    /**
     * Set the priority.
     *
     * @param string $priority
     *
     * @return self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Set the attributes.
     *
     * @param string $attributes
     *
     * @return self
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the URL.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->isExternal()) {
            return $this->path;
        }

        if (app('FrontendAsset')->getDomain() === '/' && app()->environment() !== 'local') {
            return asset($this->path, app('FrontendAsset')->isSecure());
        }

        return rtrim(app('FrontendAsset')->getDomain(), '/').'/'.ltrim($this->path, '/');
    }

    /**
     * Is this asset external?
     *
     * @return bool
     */
    public function isExternal()
    {
        return !empty($this->path) && preg_match('/(https?:)?\/\//i', $this->path);
    }

    /**
     * Store asset.
     *
     * @return self
     */
    public function store()
    {
        app('FrontendAsset')->storeAsset($this);

        return $this;
    }

    /**
     * Render this asset for the given type and location.
     *
     * @param string $type
     * @param string $location
     *
     * @return null|string
     */
    public function render()
    {
        $result = '';

        if ($this->isInline()) {
            return $this->renderInline($this->location);
        }

        switch ($this->type) {
            case 'styles':
                break;
            case 'js':
                // type, defer, async
                $result = '<script src="'.$this->getUrl().'"'.$this->renderAttributes().'></script>';
                break;
            case 'css':
                $result = '<link rel="stylesheet" type="text/css" href="'.$this->getUrl().'"'.$this->renderAttributes().'></script>';
                break;
        }

        return $result;
    }

    /**
     * Render attributes for this asset.
     *
     * @return string
     */
    private function renderAttributes()
    {
        $result = '';

        if (!is_array($this->attributes)) {
            return '';
        }

        foreach ($this->attributes as $key => $value) {
            if (is_int($key)) {
                $result .= " {$value}";
                continue;
            }

            $result .= sprintf(' %s="%s"', $key, $value);
        }

        return $result;
    }

    /**
     * Render this asset inline.
     *
     * @param string $location
     *
     * @return string
     */
    private function renderInline($location)
    {
        $result = '';
        $path = $this->path;

        if (! file_exists($path)) {

            if (! file_exists(public_path($path))) {
                return '<!-- Missing '.public_path($path).' -->';
            }

            $path = public_path($path);
        }

        $content = file_get_contents($path);

        if ($this->type === 'js' && $location === 'ready') {
            $content = sprintf('$(function(){ %s });', $content);
        }

        switch ($this->type) {
            case 'css':
                $result = sprintf('<style type="text/css"'.$this->renderAttributes().'>%s</style>', $content);
                break;
            case 'js':
                $result = sprintf('<script type="text/javascript"'.$this->renderAttributes().'>%s</script>', $content);
                break;

        }

        return $result;
    }

    private function renderReady()
    {
    }

    /**
     * Check if this asset has been marked inline.
     *
     * @return bool
     */
    private function isInline()
    {
        return stripos($this->location, 'inline') !== false || $this->location === 'ready';
    }

    /**
     * Output HTTP2 header for this asset.
     *
     * @return void
     */
    public function http2()
    {
        if ($this->isInline()) {
            return;
        }

        // Can't prefetch when asset has an integrity check.
        if (Arr::has($this->attributes, 'integrity')) {
            return;
        }

        switch ($this->type) {
            case 'js':
                $link_as = 'script';
                break;
            case 'css':
                $link_as = 'style';
                break;
            default:
                return;
        }

        header('Link: <'.$this->getUrl().'>; rel=preload; as='.$link_as.';', false);
    }

    /**
     * Magic get.
     *
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {
        if (!isset($this->$name)) {
            return;
        }

        return $this->$name;
    }
}
