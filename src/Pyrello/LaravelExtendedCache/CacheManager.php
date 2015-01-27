<?php namespace Pyrello\LaravelExtendedCache;

use Illuminate\Cache\CacheManager as BaseCacheManager,
    Illuminate\Cache\StoreInterface;

class CacheManager extends BaseCacheManager
{
    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  \Illuminate\Cache\StoreInterface  $store
     * @return \Pyrello\LaravelExtendedCache\Repository
     */
    protected function repository(StoreInterface $store)
    {
        \Log::debug('Creating repository');
        return new Repository($store);
    }
}
 