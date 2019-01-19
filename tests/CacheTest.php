<?php

namespace Ulv\LRUCache;

class CacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Redis
     */
    protected $redis;

    protected $iterations = 10000;
    protected $capacity = 9990;

    public function setUp()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1');
    }

    public function tearDown()
    {
        $keys = $this->redis->keys('ulv:lru-cache:*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    public function testPutGet()
    {
        $sut = new Cache(new MemoryStorage($this->capacity));
        $sut2 = new Cache(new RedisStorage($this->redis,$this->capacity));

        for($i=0;$i<$this->iterations;$i++) {
            $key = substr(md5(rand()), 0, 5);
            $value = substr(md5(rand()), 0, 5);
            $sut->put($key, new Node($value));
            $this->assertEquals($value, (string)$sut->get($key));
            $sut2->put($key, new Node($value));
            $this->assertEquals($value, (string)$sut2->get($key));
        }
    }

    /**
     * @dataProvider connectorsDataProvider
     */
    public function testLRU($connector)
    {
        $sut = new Cache($connector);
        $sut->put('a', new Node(10));
        $sut->put('b', new Node(20));

        // trigger usage
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
