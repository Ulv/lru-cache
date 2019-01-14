<?php

namespace Ulv\LRUCache;

/**
 * Simple in-memory KV storage
 * @package Ulv\LRUCache
 */
class MemoryStorage implements CacheConnectorInterface
{
    protected $storage = [];

    public function get($key)
    {
        return array_key_exists($key, $this->storage)
            ? $this->storage[$key]
            : null;
    }

    public function put($key, Node $value)
    {
        $this->storage[$key] = $value;
    }

    public function size()
    {
        return count($this->storage);
    }

    public function del($key)
    {
        if (array_key_exists($key, $this->storage)) {
            unset($this->storage[$key]);
        }
    }
}