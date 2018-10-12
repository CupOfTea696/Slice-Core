<?php

namespace Slice\Providers;

use Illuminate\Events\Dispatcher;

class EventProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('events', function ($app) {
            return new Dispatcher($app);
        });
    }
}
