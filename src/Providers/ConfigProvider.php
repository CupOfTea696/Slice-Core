<?php

namespace Slice\Providers;

use Exception;
use SplFileInfo;
use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Config\Repository as RepositoryContract;

class ConfigProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $items = [];
        
        $this->app->instance('config', $config = new Repository($items));
        
        $this->loadConfigurationFiles($config);
        
        date_default_timezone_set($config->get('app.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');
    }
    
    /**
     * Load the configuration items from all of the files.
     * 
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     *
     * @throws \Exception
     */
    protected function loadConfigurationFiles(RepositoryContract $repository)
    {
        $files = $this->getConfigurationFiles();
        
        if (! isset($files['app'])) {
            throw new Exception('Unable to load the "app" configuration file.');
        }
        
        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }
    
    /**
     * Get all of the configuration files for the application.
     * 
     * @return array
     */
    protected function getConfigurationFiles()
    {
        $files = [];
        $configPath = realpath($this->app->configPath());
        
        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);
            
            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }
        
        ksort($files, SORT_NATURAL);
        
        return $files;
    }
    
    /**
     * Get the configuration file nesting path.
     *
     * @param  \SplFileInfo  $file
     * @param  string  $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();
        
        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }
        
        return $nested;
    }
}
