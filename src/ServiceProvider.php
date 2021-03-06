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

        $this->app->singleton('FrontendAsset', function () {
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

            return "<?php app('FrontendAsset')->add('js', ob_get_clean(), '".$name."', 'footer-inline'); ?>";
        });

        blade::directive('captureStyle', function ($name) {
            $name = empty($name) ? 'header' : substr(str_replace('$', '', $name), 1, -1);

            return "<?php app('FrontendAsset')->add('css', ob_get_clean(), '".$name."', 'footer-inline'); ?>";
        });

        blade::directive('resources', function ($name) {
            $name = trim($name, "'\"");
            $name = "'$name'";

            return "<?php app('FrontendAsset')->autoloadAssets(['js', 'css'], $name); ?>";
        });

        blade::directive('frontendAsset', function ($name) {
            $name = trim($name, "'\"");
            $name = "$name";

            return "<?= app('FrontendAsset')->$name(); ?>";
        });

        blade::directive('asset', function ($name) {
            if (strlen(trim($name, "'\"[]")) == strlen($name)) {
                $name = "'$name'";
            }

            return "<?php app('FrontendAsset')->package($name); ?>";
        });

        blade::directive('package', function ($name) {
            if (strlen(trim($name, "'\"[]")) == strlen($name)) {
                $name = "'$name'";
            }

            return "<?php app('FrontendAsset')->package($name); ?>";
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
