<?php

namespace Ulv\LRUCache;

class CacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function setUp()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function tearDown()
    {
        $keys = $this->redis->keys('ulv:cache:lru:*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    public function testPutGet()
    {
        $sut = new Cache(new MemoryStorage(2));
        $sut->put('a', new Node(10));
        $sut->put('b', new Node(20));
        $sut->put('b', new Node(20));

        $this->assertEquals(10, (string)$sut->get('a'));
        $this->assertEquals(20, (string)$sut->get('b'));
    }

    /**
     * @dataProvider connectorsDataProvider
     */
    public function testLRU($connector)
    {
        $sut = new Cache($connector);
        $sut->put('a', new Node(10));
        $sut->put('b', new Node(20));

        $this->assertEquals(10, (string)$sut->get('a'));
        $this->assertEquals(20, (string)$sut->get('b'));

        $sut->put('c', new Node(30));

        $this->assertEquals(30, (string)$sut->get('c'));
        $this->assertEquals(20, (string)$sut->get('b'));
        $this->assertEquals(null, (string)$sut->get('a'));
    }

    public function connectorsDataProvider()
    {
        if (is_null($this->redis)) {
            $this->setUp();
        }
        return [
            [new MemoryStorage(2)],
            [new RedisStorage($this->redis, 2)],
        ];
    }

    /**
     * @dataProvider connectorsDataProvider
     */
    public function testLRU2($connector)
    {
        $sut = new Cache($connector);
        $sut->put('a', new Node(10));
        $sut->put('b', new Node(20));

        $this->assertEquals(10, (string)$sut->get('a'));
        $this->assertEquals(20, (string)$sut->get('b'));

        $sut->get('a');
        $sut->get('a');
        $sut->get('a');
        $sut->put('c', new Node(30));

        $this->assertEquals(30, (string)$sut->get('c'));
        $this->assertEquals(null, (string)$sut->get('b'));
        $this->assertEquals(10, (string)$sut->get('a'));
    }
}
