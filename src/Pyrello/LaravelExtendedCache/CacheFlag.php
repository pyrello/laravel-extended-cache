<?php namespace Pyrello\LaravelExtendedCache;

use Illuminate\Filesystem\Filesystem;

class CacheFlag
{
    /**
     * The Illuminate Filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The file cache directory
     *
     * @var string
     */
    protected $directory;

    /**
     * Create a new file cache store instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $directory
     * @return void
     */
    public function __construct(Filesystem $files, $directory)
    {
        $this->files = $files;
        $this->directory = $directory;
    }
}
 