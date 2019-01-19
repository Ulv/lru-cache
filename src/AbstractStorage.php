<?php

namespace Ulv\LRUCache;

/**
 * Abstract class for storages
 * @package Ulv\LRUCache
 */
abstract class AbstractStorage implements CacheConnectorInterface
{
    /**
     * Storage maximum capacity
     * @var int
     */
    protected $capacity = Cache::DEFAULT_CAPACITY;

    /**
     * @param int $capacity storage maximum capacity
     */
    public function __construct($capacity)
    {
        $this->capacity = intval($capacity);
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if ($this->has($key)) {
            $this->incrUsages($key);
            return $this->_get($key);
        }

        // null object
        return new Node('');
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        if ($this->_has($key)) {
            $this->incrUsages($key);
            return true;
        }

        return false;
    }

    /**
     * The real 'has' in storage
     * @param string $key key to look for
     * @return bool
     */
    abstract protected function _has($key);

    /**
     * Increments key usage in internal lookup table
     * @param string $key
     * @return void
     */
    abstract protected function incrUsages($key);

    /**
     * The real 'get' in storage
     * @param string $key
     * @return Node|null
     */
    abstract protected function _get($key);

    /**
     * @inheritdoc
     */
    public function put($key, Node $value)
    {
        $this->_put($key, $value);
        $this->removeLRU();
        $this->incrUsages($key);
    }

    /**
     * The real "put" method
     * @param $key
     * @param Node $value
     * @return void
     */
    abstract protected function _put($key, Node $value);

    /**
     * Removes least recently used item from storage (if storage capacity exceeded)
     */
    protected function removeLRU()
    {
        if ($this->capacityExceeded()) {
            $key = $this->findLRUKey();
            $this->del($key);
            $this->delFromLookup($key);
        }
    }

    /**
     * Checks if storage capacity exceeded
     * @return bool
     */
    protected function capacityExceeded()
    {
        return $this->size() > $this->capacity;
    }

    /**
     * @inheritdoc
     */
    abstract public function size();

    /**
     * Finds least recently used key in storage
     * @return string
     */
    abstract protected function findLRUKey();

    /**
     * @inheritdoc
     */
    abstract public function del($key);

    /**
     * Removes key from internal lookup table
     * @param $key
     * @return void
     */
    abstract protected function delFromLookup($key);
}