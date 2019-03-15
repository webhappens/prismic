<?php

namespace WebHappens\Prismic;

use Prismic\Cache\CacheInterface;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache as IlluminateCache;

class Cache implements CacheInterface
{
    protected $cache;

    public function __construct()
    {
        if (IlluminateCache::getStore() instanceof TaggableStore) {
            $this->cache = IlluminateCache::tags(['prismic']);
        }

        $this->cache = IlluminateCache::driver();
    }

    public function has($key)
    {
        return $this->cache->has($key);
    }

    public function get($key)
    {
        return $this->cache->get($key);
    }

    public function set($key, $value, $ttl = 0)
    {
        $this->cache->put($key, $value, now()->addSeconds($ttl));
    }

    public function delete($key)
    {
        $this->cache->forget($key);
    }

    public function clear()
    {
        $this->cache->flush();
    }
}
