# LRU cache php implementation

[Least recently used](https://en.wikipedia.org/wiki/Cache_replacement_policies#Least_Recently_Used) cache implementation with simple memory storage and redis storage

Usages:

1. Init cache storage engine (memory or redis)

1.1. Memory 
```
$cacheCapacity = 256;
$connector = new MemoryStorage($cacheCapacity);
```
1.2. Redis
```
$cacheCapacity = 256;
$queueUniqPrefix = uniqid();
$cacheTtl = 3600;
$redis = new \Redis();
$redis->connect('127.0.0.1');
$connector = new RedisStorage($redis, $cacheCapacity, $queueUniqPrefix, $cacheTtl);
```

2. Init cache

```
$cache = new Cache($connector);
```

3. Work with cache

```
$cache->put('item1', new Node(10));
print_r((string)$cache->get('item1')));

$cache->put('item2', new Node(['a' => 'b', 12]));
print_r((string)$cache->get('item2')));
```
