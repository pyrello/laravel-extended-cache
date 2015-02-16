<?php namespace Pyrello\LaravelExtendedCache;

use Carbon\Carbon;
use Closure;
use Illuminate\Cache\Repository as BaseRepository;
use Illuminate\Cache\StoreInterface;

class Repository extends BaseRepository
{
    protected $table;

    protected $sleep;

    public function __construct(StoreInterface $store)
    {
        $this->table = 'cache_flags';
        $this->sleep = 3;
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
        while ($this->cacheFlagExists($key)) {
            sleep($this->sleep);
        }

        return parent::get($key, $default);
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
            $this->createCacheFlag($key);
            $this->store->put($key, $value, $minutes);
            $this->deleteCacheFlag($key);
        }
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string   $key
     * @param  \Closure  $callback
     * @return mixed
     */
    public function rememberForever($key, Closure $callback)
    {
        // If the item exists in the cache we will just return this immediately
        // otherwise we will execute the given Closure and cache the result
        // of that execution for the given number of minutes. It's easy.
        if ( ! is_null($value = $this->get($key)))
        {
            return $value;
        }

        // If creating the cache flag is successful, finish this up
        if (!is_null($this->createCacheFlag($key))) {
            $this->forever($key, $value = $callback());
            $this->deleteCacheFlag($key);
        }
        // Otherwise, we assume that another cache flag has been created
        // and therefore we just need to get the asset as soon as its ready
        else {
            $value = $this->get($key);
        }

        return $value;
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
            return true;
        }
        // If the flag doesn't exist, return false
        return false;
    }

    public function getCacheFlag($key)
    {
        $cache_flag = \DB::table($this->table)->where('key', 'LIKE', $this->getCacheFlagKey($key) . '%')->first();
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
        try {
            // Create the cache flag
            return \DB::table($this->table)->insert([
                'key' => $this->getCacheFlagKey($key) . '_' . md5(time()),
                'created_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Delete the flag for a cached item that has been created
     *
     * @param $key
     */
    public function deleteCacheFlag($key)
    {
        \DB::table($this->table)->where('key', 'LIKE', $this->getCacheFlagKey($key) . '%')->delete();
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
