<?php

namespace Ulv\LRUCache;

/**
 * Redis as LRU storage.
 *
 * The idea is simple:
 * - uses strings for both value and usage count storage (O(1)). Usage keys imcrements with INCRBY
 * - uses set for storing/retrieving keys list (O(1))
 *
 * @package Ulv\LRUCache
 */
class RedisStorage extends AbstractStorage
{
    const DEFAULT_EXPIRE = 86400;

    /** @var  \Redis */
    protected $redis;

    protected $expire = self::DEFAULT_EXPIRE;

    protected $keysPrefix = '';

    protected $lookupKey = 'lookup-set';

    /**
     * RedisStorage constructor.
     * @param \Redis $redis
     * @param int $capacity storage maximum capacity
     * @param string $prefix redis keys prefix
     * @param int $keysExpire default keys expire (max ttl)
     */
    public function __construct(\Redis $redis, $capacity, $prefix = 'ulv:lru-cache',
                                $keysExpire = self::DEFAULT_EXPIRE)
    {
        parent::__construct($capacity);

        $this->redis = $redis;
        $this->keysPrefix = rtrim($prefix, ':');
        $this->expire = $keysExpire;
        $this->lookupKey = $this->buildKey($this->lookupKey);
    }

    /**
     * Builds redis key
     * @param $key
     * @return string
     */
    protected function buildKey($key, $isLookup = false)
    {
        $key = sprintf('%s:%s', $this->keysPrefix, $key);
        if ($isLookup) {
            $key .= ':usages';
        }
        return $key;
    }

    /**
     * @inheritdoc
     */
    public function size()
    {
        return $this->redis->sCard($this->lookupKey) + 1;
    }

    /**
     * @inheritdoc
     */
    public function del($key)
    {
        $this->redis->del($this->buildKey($key));
        $this->delFromLookup($key);
    }

    /**
     * @inheritdoc
     */
    protected function delFromLookup($key)
    {
        $this->redis->sRem($this->lookupKey, $key);
        $this->redis->del($this->buildKey($key, true));
    }

    /**
     * @inheritdoc
     */
    protected function incrUsages($key)
    {
        $rkey = $this->buildKey($key, true);
        $this->redis->incr($rkey);
        $this->redis->expire($rkey, $this->expire);
        $this->redis->sAdd($this->lookupKey, $key);
    }

    /**
     * @inheritdoc
     */
    protected function findLRUKey()
    {
        $keys = $this->redis->sMembers($this->lookupKey);

        $minUsages = 0;
        $minUsageKey = '';
        foreach ($keys as $key) {
            $rkey = $this->buildKey($key, true);
            $value = $this->redis->get($rkey);
            if ($minUsages == 0) {
                $minUsages = $value;
                $minUsageKey = $key;
            } elseif ($value < $minUsages) {
                $minUsages = $value;
                $minUsageKey = $key;
            }
        }

        return $minUsageKey;
    }

    /**
     * @inheritdoc
     */
    protected function _get($key)
    {
        return $this->redis->get($this->buildKey($key));
    }

    /**
     * @inheritdoc
     */
    protected function _has($key)
    {
        return $this->redis->exists($this->buildKey($key));
    }

    /**
     * @inheritdoc
     */
    protected function _put($key, Node $value)
    {
        $this->redis->setex($this->buildKey($key), $this->expire, (string)$value);
    }
}