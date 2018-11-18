<?php declare(strict_types=1);

namespace App\PhpRedis\Tests\Functional;

use App\PhpRedisCache\RedisCacheItem;
use App\PhpRedisCache\RedisCacheItemPool;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Connection\Parameters;
use Psr\Cache\CacheItemInterface;

class RedisCacheItemPoolTest extends TestCase
{
    /** @var RedisCacheItemPool */
    private $cachePool;

    public function setUp()
    {
        $client = new Client(new Parameters([
            'host' => 'redis',
            'database' => '15',
        ]));

        $this->cachePool = new RedisCacheItemPool($client);
    }

    public function testCacheItemPoolCreateNewItem()
    {
        $newCacheItem = $this->cachePool->getItem('test');

        $this->assertInstanceOf(CacheItemInterface::class, $newCacheItem);
        $this->assertFalse($newCacheItem->isHit());
        $this->assertEquals('test', $newCacheItem->getKey());
        $this->assertNull($newCacheItem->get());
    }


    public function testCacheItemPoolSaveItem()
    {
        $this->assertFalse($this->cachePool->hasItem('test'));

        $newCacheItem = $this->cachePool->getItem('test');
        $this->cachePool->save($newCacheItem);

        $this->assertTrue($this->cachePool->hasItem('test'));
    }

    public function testCacheItemPoolGetExistingItem()
    {
        $newCacheItem = $this->cachePool->getItem('test');
        $newCacheItem->set('It works!');
        $this->cachePool->save($newCacheItem);

        $existingItem = $this->cachePool->getItem('test');

        $this->assertInstanceOf(RedisCacheItem::class, $existingItem);
        $this->assertTrue($existingItem->isHit());
        $this->assertEquals('test', $existingItem->getKey());
        $this->assertEquals('It works!', $existingItem->get());
    }

    public function testCacheItemPoolSaveDeffered()
    {
        $newCacheItem = $this->cachePool->getItem('test');
        $this->cachePool->saveDeferred($newCacheItem);

        $this->assertTrue($this->cachePool->hasItem('test'));

        $this->cachePool->commit();

        $this->assertTrue($this->cachePool->hasItem('test'));
    }

    public function testCacheItemPoolClear()
    {
        $newCacheItem = $this->cachePool->getItem('test');
        $this->cachePool->save($newCacheItem);

        $this->assertTrue($this->cachePool->hasItem('test'));

        $this->cachePool->clear();

        $this->assertFalse($this->cachePool->hasItem('test'));
    }

    public function testCacheItemPoolClearWithDeferred()
    {
        $newCacheItem = $this->cachePool->getItem('test');
        $this->cachePool->saveDeferred($newCacheItem);

        $this->assertTrue($this->cachePool->hasItem('test'));

        $this->cachePool->clear();
        $this->cachePool->commit();

        $this->assertFalse($this->cachePool->hasItem('test'));
    }

    public function testCacheItemPoolDelete()
    {
        $newCacheItem = $this->cachePool->getItem('test');
        $this->cachePool->save($newCacheItem);

        $this->assertTrue($this->cachePool->hasItem('test'));

        $this->cachePool->deleteItem('test');

        $this->assertFalse($this->cachePool->hasItem('test'));
    }

    public function testCacheItemPoolGetExpired()
    {
        $newCacheItem = $this->cachePool->getItem('test');
        $newCacheItem->expiresAfter(2);
        $this->cachePool->save($newCacheItem);

        sleep(3);
        $expiredCacheItem = $this->cachePool->getItem('test');
        $this->assertFalse($expiredCacheItem->isHit());
        $this->assertNull($expiredCacheItem->get());
    }

    public function tearDown()
    {
        $this->cachePool->clear();
    }
}
