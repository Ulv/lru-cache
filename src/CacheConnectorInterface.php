<?php

namespace Ulv\LRUCache;

/**
 * Interface CacheConnectorInterface
 * @package Ulv\LRUCache
 */
interface CacheConnectorInterface
{
    /**
     * Returns value by key (or null object if key not exist)
     * @param string $key key to lookup for
     * @return Node
     */
    public function get($key);

    /**
     * Puts value in storage. Also removes least recently used item from internal KV-storage/lookup
     * table.
     * @param string $key
     * @param Node $value
     */
    public function put($key, Node $value);

    /**
     * Returns current storage size
     * @return int
     */
    public function size();

    /**
     * Removes item from storage
     * @param $key
     * @return void
     */
    public function del($key);

    /**
     * Returns true if key exists in storage (or false otherwise)
     * @param $key
     * @return bool
     */
    public function has($key);
}