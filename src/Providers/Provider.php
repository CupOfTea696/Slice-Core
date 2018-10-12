<?php

namespace Slice\Providers;

use Illuminate\Container\Container;

abstract class Provider
{
    /**
     * The Application container.
     * 
     * @var \Illuminate\Container\Container
     */
    protected $app;
    
    /**
     * Create a new Provider instance.
     * 
     * @param \Illuminate\Container\Container $app
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }
    
    /**
     * Register the Provider.
     * 
     * @return void
     */
    abstract public function register();
}
