<?php

namespace Slice\Providers;

use Illuminate\Filesystem\Filesystem;

class FilesystemProvider extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('files', function () {
            return new Filesystem;
        });
    }
}
