<?php

namespace Slice;

use Illuminate\Container\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class App extends Container
{
    /**
     * The Application's root path.
     * 
     * @var string
     */
    protected $path;
    
    /**
     * The Application's code Providers.
     * 
     * @var string
     */
    protected $providers = [
        Providers\ConfigProvider::class,
        Providers\ViewProvider::class,
        Providers\EventProvider::class,
        Providers\RouterProvider::class,
        Providers\FilesystemProvider::class,
    ];
    
    /**
     * Create a new Slice Application instance.
     * 
     * @param string $path
     * @return void
     */
    public function __construct($path)
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
        
        $this->setBasePath($path);
        
        $this->registerCoreProviders();
        $this->registerCoreContainerAliases();
        
        $this->boot();
        
        $this->loadRoutes();
    }
    
    public function boot()
    {
        //
    }
    
    /**
     * Register the core providers.
     * 
     * @return void
     */
    public function registerCoreProviders()
    {
        foreach ($this->providers as $provider) {
            $this->make($provider)->register();
        }
    }
    
    /**
     * Load the Application's routes.
     * 
     * @return void
     */
    public function loadRoutes()
    {
        $this->router->prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
        
        $this->router->middleware('web')
            ->group(base_path('routes/web.php'));
    }
    
    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }
    
    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);
        
        if ($this->bound('translator')) {
            $this['translator']->setLocale($locale);
        }
        
        $this['events']->dispatch(new Events\LocaleUpdated($locale));
    }
    
    /**
     * Determine if application locale is the given locale.
     *
     * @param  string  $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }
    
    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
            'app'                  => [\Slice\App::class, \Illuminate\Contracts\Container\Container::class, \Psr\Container\ContainerInterface::class],
            'blade.compiler'       => [\Illuminate\View\Compilers\BladeCompiler::class],
            'config'               => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
            'events'               => [Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
            'files'                => [\Illuminate\Filesystem\Filesystem::class],
            'redirect'             => [\Illuminate\Routing\Redirector::class],
            'request'              => [\Illuminate\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
            'router'               => [\Illuminate\Routing\Router::class, \Illuminate\Contracts\Routing\Registrar::class, \Illuminate\Contracts\Routing\BindingRegistrar::class],
            'url'                  => [\Illuminate\Routing\UrlGenerator::class, \Illuminate\Contracts\Routing\UrlGenerator::class],
            'view'                 => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
    
    /**
     * Get the base path of the installation.
     *
     * @param  string  $path Optionally, a path to append to the base path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->path . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
    
    /**
     * Set the base path of the installation.
     *
     * @param  string  $path
     * @return void
     */
    public function setBasePath($path)
    {
        $this->path = $path;
    }
    
    /**
     * Get the path to the application configuration files.
     *
     * @param  string  $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath('config' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
    
    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->basePath('public' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
    
    /**
     * Get the path to the routes file.
     * 
     * @return string
     */
    public function routesPath()
    {
        if (! $this->routes) {
            $this->routes = $this->basePath('routes.php');
        }
        
        return $this->routes;
    }
    
    /**
     * Set the path to the routes file.
     *
     * @param  string  $path
     * @return void
     */
    public function setRoutesPath($path)
    {
        $this->routes = $path;
    }
    
    /**
     * Get the resource path.
     *
     * @param  string  $path Optionally, a path to append to the base path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath('resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
    
    /**
     * Get the storage path.
     *
     * @param  string  $path Optionally, a path to append to the base path
     * @return string
     */
    public function storagePath($path = '')
    {
        return $this->basePath('storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
    
    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        }
        
        throw new HttpException($code, $message, null, $headers);
    }
}
