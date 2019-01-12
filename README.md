# LRU cache php implementation

[Least recently used](https://en.wikipedia.org/wiki/Cache_replacement_policies#Least_Recently_Used) cache implementation with simple memory storage and redis storage

Usages:

1. Init cache storage engine (memory or redis)

1.1. Memory 
```
$connector = new MemoryStorage();
```
1.2. Redis
```
$queueUniqPrefix = uniqid();
$cacheTtl = 3600;
$redis = new \Redis();
$redis->connect('127.0.0.1');
$connector = new RedisStorage($redis, $queueUniqPrefix, $cacheTtl);
```

2. Init cache

```
$cacheCapacity = 256;
$cache = new Cache($cacheCapacity, $connector);
```

3. Work with cache

```
$cache->put('item1', new Node(10));
print_r((string)$cache->get('item1')));

$cache->put('item2', new Node(['a' => 'b', 12]));
print_r((string)$cache->get('item2')));
```
