<?php

namespace Ulv\LRUCache;

class MemoryStorage extends AbstractStorage
{
    protected $storage = [];

    protected $lookup = [];

    public function size()
    {
        return count($this->storage);
    }

    public function del($key)
    {
        if ($this->has($key)) {
            unset($this->storage[$key]);
        }
    }

    protected function incrUsages($key)
    {
        if (array_key_exists($key, $this->lookup)) {
            $this->lookup[$key]++;
        } else {
            $this->lookup[$key] = 1;
        }
    }

    protected function findLRUKey()
    {
        $lruItemIndex = array_keys($this->lookup, min($this->lookup));
        return reset($lruItemIndex);
    }

    protected function delFromLookup($key)
    {
        unset($this->lookup[$key]);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function _get($key)
    {
        return $this->storage[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    protected function _has($key)
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * @param $key
     * @param Node $value
     */
    protected function _put($key, Node $value)
    {
        $this->storage[$key] = $value;
    }
}