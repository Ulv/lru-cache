<?php

namespace Ulv\LRUCache;

class Node
{
    protected $data = '';

    public function __construct($data)
    {
        $this->data = $this->stringify($data);
    }

    protected function stringify($data)
    {
        return is_string($data) ? $data : json_encode($data);
    }

    public function __toString()
    {
        return $this->data;
    }
}