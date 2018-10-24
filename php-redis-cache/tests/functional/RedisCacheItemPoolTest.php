<?php declare(strict_types=1);

namespace App\PhpRedis\Tests\functional;

use App\PhpRedisCache\RedisCacheItem;
use App\PhpRedisCache\RedisCacheItemPool;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Connection\Parameters;

class RedisCacheItemPoolTest extends TestCase
{
    public function testCacheItemPool()
    {
        $client = new Client(new Parameters([
            'host' => 'redis',
        ]));

        $cachePool = new RedisCacheItemPool($client);

        $newCacheItem = RedisCacheItem::create('test', 'It works!');
        $cachePool->save($newCacheItem);

        $receivedCacheItem = $cachePool->getItem('test');

        // TODO: fix cacheItem expiration
        var_dump($newCacheItem, $receivedCacheItem);

        $this->assertEquals($newCacheItem, $receivedCacheItem);
    }
}
