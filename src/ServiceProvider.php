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
        $this->mergeConfigFrom(__DIR__.'/../config/config.default.php', 'hnhdigital.assets');
        $this->mergeConfigFrom(__DIR__.'/../config/config.packages.php', 'hnhdigital.assets.packages');

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
            __DIR__.'/../config/config.php' => config_path('hnhdigital/assets.php'),
        ]);

        blade::directive('captureScript', function ($name) {
            $name = empty($name) ? 'inline' : substr(str_replace('$', '', $name), 1, -1);

            return "<?php FrontendAsset::addScript(ob_get_clean(), '".$name."'); ?>";
        });

        blade::directive('captureStyle', function ($name) {
            $name = empty($name) ? 'header' : substr(str_replace('$', '', $name), 1, -1);

            return "<?php FrontendAsset::addStyle(ob_get_clean(), '".$name."'); ?>";
        });

        blade::directive('resources', function ($name) {
            $name = trim($name, "'\"");
            $name = "'$name'";

            return "<?php FrontendAsset::controller(['js', 'css'], $name); ?>";
        });

        blade::directive('frontendAsset', function ($name) {
            $name = trim($name, "'\"");
            $name = "$name";

            return "<?php FrontendAsset::$name(); ?>";
        });

        blade::directive('asset', function ($name) {
            if (strlen(trim($name, "'\"[]")) == strlen($name)) {
                $name = "'$name'";
            }

            return "<?php FrontendAsset::container($name); ?>";
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
