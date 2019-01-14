<?php

namespace Ulv\LRUCache;

class Cache
{
    const DEFAULT_CAPACITY = 65534;

    protected $capacity;

    protected $lookup = [];

    /**
     * @var CacheConnectorInterface
     */
    protected $storage;

    public function __construct($capacity = Cache::DEFAULT_CAPACITY, CacheConnectorInterface $connector = null)
    {
        $this->capacity = $capacity ?: Cache::DEFAULT_CAPACITY;
        $this->storage = $connector ?: new MemoryStorage();
    }

    public function get($key)
    {
        if ($value = $this->storage->get($key)) {
            $this->increaseItemUsages($key);
        }
        return $value;
    }

    /**
     * @param $key
     */
    protected function increaseItemUsages($key)
    {
        if (array_key_exists($key, $this->lookup)) {
            $this->lookup[$key]++;
        } else {
            $this->lookup[$key] = 1;
        }
    }

    public function put($key, Node $value)
    {
        if (array_key_exists($key, $this->lookup)) {
            $this->lookup[$key]++;
        } else {
            if ($this->capacityExceeded()) {
                $this->removeLRUItem($this->getLRUKey());
            }
            $this->lookup[$key] = 1;
        }
        $this->storage->put($key, $value);
    }

    /**
     * @return bool
     */
    protected function capacityExceeded()
    {
        return $this->storage->size() >= $this->capacity;
    }

    /**
     * @return integer
     */
    protected function getLRUKey()
    {
        $lruItemIndex = array_keys($this->lookup, min($this->lookup));
        return reset($lruItemIndex);
    }

    /**
     * @param $lruItemIndex
     */
    protected function removeLRUItem($lruItemIndex)
    {
        $this->storage->del($lruItemIndex);
        unset($this->lookup[$lruItemIndex]);
    }
}