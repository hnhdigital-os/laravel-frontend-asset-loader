<?php

namespace HnhDigital\LaravelFrontendAssetLoader\Tests;

use HnhDigital\LaravelFrontendAssetLoader\Resource;
use PHPUnit\Framework\TestCase;
use Roumen\Asset\Asset;

class ResourceTest extends TestCase
{
    /**
     * Test the add method.
     *
     * @return void
     */
    public function testAdd()
    {
        $res = new Resource();

        $asset = 'style.css';
        $res->add($asset);
        $this->assertEquals(true, isset(Asset::$css['/asset/'.$asset]));
    }

    /**
     * Test the addScript method.
     *
     * @return void
     */
    public function testAddScript()
    {
        $res = new Resource();

        $asset = 'alert(\'test1\');';
        $res->addScript($asset);
        $this->assertEquals($asset, Asset::$scripts['footer'][0]);
    }

    /**
     * Test the reverseStylesOrder method.
     *
     * @return void
     */
    public function testReverseStylesOrder()
    {
        Asset::$scripts['footer'] = [];
        $res = new Resource();

        $asset1 = 'alert(\'test1\');';
        $res->addScript($asset1);

        $asset2 = 'alert(\'test2\');';
        $res->addScript($asset2);

        $res->reverseStylesOrder();

        $scripts = array_values(Asset::$scripts['footer']);

        $this->assertEquals($asset1, Asset::$scripts['footer'][0]);
        $this->assertEquals($asset2, Asset::$scripts['footer'][1]);
        $this->assertEquals($asset2, $scripts[0]);
        $this->assertEquals($asset1, $scripts[1]);
    }

    /**
     * Test the addStyle method.
     *
     * @return void
     */
    public function testAddStyle()
    {
        Asset::$styles = [];

        $res = new Resource();

        $asset = 'body { color: #FFF; }';
        $res->addStyle($asset);
        $this->assertEquals($asset, Asset::$styles['header'][0]);
    }

    /**
     * Test the addFirst method.
     *
     * @return void
     */
    public function testAddFirst()
    {
        Asset::$css = [];

        $res = new Resource();

        $asset = 'style.css';
        $res->add($asset);

        $asset = 'style1.css';
        $res->addFirst($asset);

        $styles = array_keys(Asset::$css);
        $this->assertEquals('/asset/'.$asset, $styles[0]);
    }

    /**
     * Test the addAfter method.
     *
     * @return void
     */
    public function testAddAfter()
    {
        Asset::$css = [];

        $res = new Resource();

        $asset1 = 'style.css';
        $res->add($asset1);

        $asset2 = 'style1.css';
        $res->add($asset2);

        $asset3 = 'style2.css';
        $res->addAfter($asset3, $asset1);

        $styles = array_keys(Asset::$css);

        $this->assertEquals('/asset/'.$asset3, $styles[1]);
    }

    /**
     * Test the container method.
     *
     * @return void
     */
    public function testContainer()
    {
        $res = new Resource();
        $res->container('jquery');
    }

    /**
     * Test the containers method.
     *
     * @return void
     */
    public function testContainers()
    {
    }

    /**
     * Test the controller method.
     *
     * @return void
     */
    public function testController()
    {
    }

    /**
     * Test the css method.
     *
     * @return void
     */
    public function testCss()
    {
    }

    /**
     * Test the less method.
     *
     * @return void
     */
    public function testLess()
    {
    }

    /**
     * Test the styles method.
     *
     * @return void
     */
    public function testStyles()
    {
    }

    /**
     * Test the js method.
     *
     * @return void
     */
    public function testJs()
    {
    }

    /**
     * Test the scripts method.
     *
     * @return void
     */
    public function testScripts()
    {
    }

    /**
     * Test elixir method.
     *
     * @return void
     */
    public function testElixir()
    {
        global $env;

        $res = new Resource();

        // External URL's
        $url = 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png';
        $this->assertEquals($res->elixir($url), $url);

        $asset = 'style.css';
        $this->assertEquals('/asset/'.$asset, $res->elixir($asset));

        $env['APP_ASSET_SOURCE'] = 'build';
        $this->assertEquals('/build/style.123456.css', $res->elixir($asset));

        $asset = 'logo.png';
        file_put_contents(__DIR__.'/temp/'.$asset, '');
        $this->assertEquals($asset, $res->elixir($asset));
        unlink(__DIR__.'/temp/'.$asset);

        file_put_contents(__DIR__.'/temp/assets/'.$asset, '');
        $this->assertEquals('/assets/'.$asset, $res->elixir($asset));
        unlink(__DIR__.'/temp/assets/'.$asset);

        $asset = 'logo1.png';
        $this->assertEquals('', $res->elixir($asset));
    }
}
