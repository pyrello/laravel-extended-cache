<?php namespace Pyrello\LaravelExtendedCache;

use Carbon\Carbon;
use Illuminate\Cache\Repository as BaseRepository;
use Illuminate\Cache\StoreInterface;

class Repository extends BaseRepository
{
    protected $table;

    protected $sleep;

    public function __construct(StoreInterface $store)
    {
        \Log::debug('Running constructor for laravel extended cache repository');
//        $this->table = \Config::get('laravel-extended-cache::table');
        $this->table = 'cache_flags';
        $this->sleep = 2;
        parent::__construct($store);
    }

    /**
     * Retrieve an item from the cache by key. If the item is about to expire soon,
     * extend the existing cache entry (for other requests) before returning the item
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        \Log::debug('Running cache::get through laravel extended cache');
        //todo: check cache flag, if it exists, sleep until it doesn't

        while ($this->cacheFlagExists($key)) {
            \Log::debug('Waiting for cache to finish generating...');
            sleep($this->sleep);
        }

        parent::get($key, $default);
    }

    /**
     * Store an item in the cache. Add the amount of time to cache it for
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  \DateTime|int  $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $minutes = $this->getMinutes($minutes);

        if ( ! is_null($minutes))
        {
            $this->createCacheFlag($key, $value, $minutes);
            $this->store->put($key, $value, $minutes);
            $this->deleteCacheFlag($key);
        }
    }

    /**
     * Check if there is a cache flag for a particular key in the cache
     *
     * @param $key
     * @return bool
     */
    public function cacheFlagExists($key)
    {
        // If the flag exists, return true
        if (!is_null($this->getCacheFlag($key))) {
            \Log::debug('Cache flag exists...');
            return true;
        }
        // If the flag doesn't exist, return false
        \Log::debug('Cache flag does not exist...');
        return false;
    }

    public function getCacheFlag($key)
    {
        \Log::debug('Getting the cache flag for ' . $key . '...');
        $cache_flag = \DB::table($this->table)->where('key', '=', $this->getCacheFlagKey($key))->first();
        // todo: abstract this into a separate layer so it doesn't call the database directly.
        return $cache_flag;
    }

    /**
     * Create the flag for a cached item that is being created
     *
     * @param $key
     * @return bool
     */
    public function createCacheFlag($key)
    {
        \Log::debug('Creating the cache flag for ' . $key . '...');
        // Create the cache flag
        return \DB::table($this->table)->insert([
            'key' => $this->getCacheFlagKey($key),
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Delete the flag for a cached item that has been created
     *
     * @param $key
     */
    public function deleteCacheFlag($key)
    {
        \Log::debug('Deleting cache flag for ' . $key . '...');
        \DB::table($this->table)->where('key', '=', $this->getCacheFlagKey($key))->delete();
    }

    /**
     * Generate a key for the cache flag
     *
     * @param $key
     * @return mixed
     */
    public function getCacheFlagKey($key)
    {
        return $key;
    }

}
