<?php

namespace Slice\Providers;

use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Compilers\BladeCompiler;

class ViewProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return new BladeCompiler(
                $app['files'], $app['config']['view.compiled']
            );
        });
        
        $this->app->singleton('view.engine.resolver', function ($app) {
            $resolver = new EngineResolver;
            
            $resolver->register('blade', function () use ($app) {
                return new CompilerEngine($app['blade.compiler']);
            });
            
            $resolver->register('php', function () {
                return new PhpEngine;
            });
            
            return $resolver;
        });
        
        $this->app->bind('view.finder', function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        });
        
        $this->app->singleton('view', function ($app) {
            $resolver = $app['view.engine.resolver'];
            $finder = $app['view.finder'];
            $events = $app['events'];
            
            $factory = new Factory($resolver, $finder, $events);
            
            $factory->setContainer($app);
            $factory->share('app', $app);
            
            return $factory;
        });
    }
}
