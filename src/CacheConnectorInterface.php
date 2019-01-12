<?php

namespace Ulv\LRUCache;

interface CacheConnectorInterface
{
    public function get($key);

    public function put($key, Node $value);

    public function size();

    public function del($key);
}