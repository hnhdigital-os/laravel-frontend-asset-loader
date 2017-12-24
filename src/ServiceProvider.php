<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

use Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('FrontendAsset', function () {
            return new FrontendAsset();
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('frontend-assets.php'),
        ]);

        blade::directive('captureScript', function ($name) {
            $name = empty($name) ? 'inline' : substr(str_replace('$', '', $name), 1, -1);

            return "<?php FrontendAsset::addScript(ob_get_clean(), '".$name."'); ?>";
        });

        blade::directive('captureStyle', function ($name) {
            $name = empty($name) ? 'header' : substr(str_replace('$', '', $name), 1, -1);

            return "<?php FrontendAsset::addStyle(ob_get_clean(), '".$name."'); ?>";
        });

        blade::directive('resources', function ($template) {
            $template = addslashes(substr($template, 1, -1));

            return "<?php FrontendAsset::controller(['js', 'css'], '$template'); ?>";
        });

        blade::directive('asset', function ($asset) {
            $asset = addslashes(substr($asset, 1, -1));

            return "<?php FrontendAsset::container('$asset'); ?>";
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['FrontendAsset'];
    }
}
