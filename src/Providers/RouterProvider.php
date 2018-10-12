<?php

namespace Slice\Providers;

use Slice\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\URL;

class RouterProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });
        
        Request::macro('hasValidSignature', function () {
            return URL::hasValidSignature($this);
        });
    }
}
