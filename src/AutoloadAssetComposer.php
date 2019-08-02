<?php

namespace HnhDigital\LaravelFrontendAssetLoader;

use Illuminate\Contracts\View\View;

class AutoloadAssetComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        app('FrontendAsset')->autoloadAssets(['js', 'css'], $view->name());
    }
}