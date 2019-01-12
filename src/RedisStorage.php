<?php

namespace Ulv\LRUCache;

/**
 * Redis KV storage
 * @package Ulv\LRUCache
 */
class RedisStorage implements CacheConnectorInterface
{
    const CACHE_KEY_PREFIX = 'ulv:cache:lru:';

    const DEFAULT_CACHE_TTL = 86400;

    /**
     * @var \Redis
     */
    protected $redis;

    protected $key = self::CACHE_KEY_PREFIX;

    protected $storageSizeKey = '';

    protected $cacheTtl = self::DEFAULT_CACHE_TTL;

    public function __construct(\Redis $redis,
                                $keyUniqid = '',
                                $defaultCacheItemTtl = self::DEFAULT_CACHE_TTL
    )
    {
        $this->redis = $redis;
        $this->key = sprintf("%s%s", self::CACHE_KEY_PREFIX, $keyUniqid ?: 'hash');
        $this->cacheTtl = $defaultCacheItemTtl;
    }

    public function get($key)
    {
        return $this->redis->hGet($this->key, $key);
    }

    public function put($key, Node $value)
    {
        $this->redis->hSet($this->key, $key, (string)$value);
        $this->redis->expire($this->key, $this->cacheTtl);
    }

    public function size()
    {
        return $this->redis->hLen($this->key);
    }

    public function del($key)
    {
        $this->redis->hDel($this->key, $key);
    }
}