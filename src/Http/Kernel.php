<?php

namespace Slice\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\Pipeline;
use Illuminate\Routing\Redirector;
use Illuminate\Container\Container;
use Illuminate\Routing\UrlGenerator;

class Kernel
{
    /**
     * The Application container.
     * 
     * @var \Illuminate\Container\Container
     */
    protected $app;
    
    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;
    
    /**
     * The application's middleware stack.
     *
     * @var array
     */
    protected $middleware = [];
    
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];
    
    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [];
    
    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ];
    
    /**
     * Create a new Kernel instance.
     * 
     * @param \Illuminate\Container\Container $app
     * @return void
     */
    public function __construct(Container $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;
        
        $router->middlewarePriority = $this->middlewarePriority;
        
        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->middlewareGroup($key, $middleware);
        }
        
        foreach ($this->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        $redirect = new Redirector(new UrlGenerator($this->router->getRoutes(), $request));
        
        $this->app->instance(Request::class, $request);
        $this->app->instance(Redirector::class, $redirect);
        
        return (new Pipeline($this->app))
            ->send($request)
            ->through($this->middleware)
            ->then($this->dispatchToRouter());
    }
    
    /**
     * Get the route dispatcher callback.
     *
     * @return \Closure
     */
    protected function dispatchToRouter()
    {
        return function ($request) {
            $this->app->instance('request', $request);
            
            return $this->router->dispatch($request);
        };
    }
    
    /**
     * Call the terminate method on any terminable middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        $this->terminateMiddleware($request, $response);
    }
    
    /**
     * Call the terminate method on any terminable middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    protected function terminateMiddleware($request, $response)
    {
        $middlewares = $this->app->shouldSkipMiddleware() ? [] : array_merge(
            $this->gatherRouteMiddleware($request),
            $this->middleware
        );
        
        foreach ($middlewares as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }
            
            [$name] = $this->parseMiddleware($middleware);
            
            $instance = $this->app->make($name);
            
            if (method_exists($instance, 'terminate')) {
                $instance->terminate($request, $response);
            }
        }
    }
    
    /**
     * Gather the route middleware for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function gatherRouteMiddleware($request)
    {
        if ($route = $request->route()) {
            return $this->router->gatherRouteMiddleware($route);
        }
        
        return [];
    }
    
    /**
     * Parse a middleware string to get the name and parameters.
     *
     * @param  string  $middleware
     * @return array
     */
    protected function parseMiddleware($middleware)
    {
        [$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, []);
        
        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }
        
        return [$name, $parameters];
    }
    
    /**
     * Determine if the kernel has a given middleware.
     *
     * @param  string  $middleware
     * @return bool
     */
    public function hasMiddleware($middleware)
    {
        return in_array($middleware, $this->middleware);
    }
    
    /**
     * Add a new middleware to beginning of the stack if it does not already exist.
     *
     * @param  string  $middleware
     * @return $this
     */
    public function prependMiddleware($middleware)
    {
        if (array_search($middleware, $this->middleware) === false) {
            array_unshift($this->middleware, $middleware);
        }
        
        return $this;
    }
    
    /**
     * Add a new middleware to end of the stack if it does not already exist.
     *
     * @param  string  $middleware
     * @return $this
     */
    public function pushMiddleware($middleware)
    {
        if (array_search($middleware, $this->middleware) === false) {
            $this->middleware[] = $middleware;
        }
        
        return $this;
    }
};
