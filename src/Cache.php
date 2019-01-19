<?php

namespace Ulv\LRUCache;

class Cache
{
    const DEFAULT_CAPACITY = 65534;

    /**
     * @var CacheConnectorInterface
     */
    protected $storage;

    public function __construct(CacheConnectorInterface $storage = null)
    {
        $this->storage = $storage ?: new MemoryStorage(self::DEFAULT_CAPACITY);
    }

    public function get($key)
    {
        return $this->storage->get($key);
    }

    public function put($key, Node $value)
    {
        $this->storage->put($key, $value);
    }
}