<?php declare(strict_types = 1);

namespace Cstea\ApiBundle\Traits;

use Psr\Cache\CacheItemPoolInterface;

trait CacheableAware
{
    use LoggerAware;
    
    /** @var CacheItemPoolInterface */
    private $cacheAdapter;

    /**
     * Setter Injector for Cache adapter
     *
     * @required
     * @param CacheItemPoolInterface $adapter Cache adapter.
     */
    public function setCacheAdapter(CacheItemPoolInterface $adapter): void
    {
        $this->cacheAdapter = $adapter;
    }

    /**
     * Cache wrapper
     *
     * @param string   $key      Cache ID/Key.
     * @param callable $callable Function that returns data to store in cache.
     * @param int      $ttl      Cache duration in seconds.
     * @return mixed
     */
    protected function cache(string $key, callable $callable, int $ttl = 3600)
    {
        if ($this->cacheAdapter !== null) {
            $cacheItem = $this->cacheAdapter->getItem($key);
            if ($cacheItem->isHit()) {
                $data = $cacheItem->get();
                $this->getLogger()->notice('Hitting cached data', ['key' => $key]);
                $this->getLogger()->debug('Fetching from cache', ['data' => $data]);
                return $data;
            }
        }
        
        $result = $callable();
        
        if ($this->cacheAdapter !== null) {
            $cacheItem->set($result);
            $cacheItem->expiresAfter($ttl);
            $this->getLogger()->notice('Saving cached item', ['key' => $key, 'ttl' => $ttl]);
            $this->getLogger()->debug('Item to cache', ['data' => $result]);
            $this->cacheAdapter->save($cacheItem);
            
            $cacheKeys = $this->cacheAdapter->getItem('cstea.apibundle.cacheKeys');
            $keys = [];
            if ($cacheKeys->isHit()) {
                $keys = $cacheKeys->get();
            }
            if (!\in_array($key, $keys)) {
                $keys[] = $key;
                $cacheKeys->set($keys);
                $this->cacheAdapter->save($cacheKeys);
            }
        }
        
        return $result;
    }

    /**
     * Invalidate keys that share the same namespace.
     *
     * @param string $namespace Namespace to invalidate.
     */
    protected function invalidateCache(string $namespace): void
    {
        if ($this->cacheAdapter !== null) {
            $cacheKeys = $this->cacheAdapter->getItem('cstea.apibundle.cacheKeys');
            if ($cacheKeys->isHit()) {
                $cachedKeys = $cacheKeys->get();
                $deleteKeys = \array_filter($cachedKeys, static function ($key) use ($namespace) {
                    return \stripos($key, $namespace) === 0;
                });
                $this->getLogger()->notice('Invalidating cache items', ['keys' => $deleteKeys]);
                $this->cacheAdapter->deleteItems($deleteKeys);
                $cacheKeys->set(\array_diff($cachedKeys, $deleteKeys));
                $this->cacheAdapter->save($cacheKeys);
            }
        }
    }

}