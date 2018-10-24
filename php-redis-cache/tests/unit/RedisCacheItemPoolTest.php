<?php declare(strict_types=1);

namespace App\PhpRedisCache\Tests\Unit;

use App\PhpRedisCache\RedisCacheItem;
use App\PhpRedisCache\RedisCacheItemPool;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

class RedisCacheItemPoolTest extends TestCase
{
    /**
     * @dataProvider provideInvalidKey
     */
    public function testGetItemInvalidArgumentException($invalidKey)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItemPool->getItem($invalidKey);
    }

    public function testGetItemNotExists()
    {
        $client = $this->prophesize(Client::class);
        $client->exists('test-key')->willReturn(false);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItem = $cacheItemPool->getItem('test-key');

        $this->assertInstanceOf(CacheItemInterface::class, $cacheItem);
        $this->assertNull($cacheItem->get());
        $this->assertFalse($cacheItem->isHit());
    }

    public function testGetItem()
    {
        $client = $this->prophesize(Client::class);
        $client->exists('test-key')->willReturn(true);
        $client->get('test-key')->willReturn('test-value');
        $client->ttl('test-key')->willReturn(5);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItem = $cacheItemPool->getItem('test-key');

        $this->assertInstanceOf(CacheItemInterface::class, $cacheItem);
        $this->assertEquals( 'test-value', $cacheItem->get());
        $this->assertTrue($cacheItem->isHit());
    }

    /**
     * @dataProvider provideInvalidKey
     */
    public function testGetItemsInvalidArgumentException($invalidKey)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItemPool->getItems([$invalidKey]);
    }

    public function testGetItemsNoKeys()
    {
        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItems = $cacheItemPool->getItems([]);

        $this->assertInternalType('array', $cacheItems);
        $this->assertCount( 0, $cacheItems);
    }

    public function testGetItems()
    {
        $client = $this->prophesize(Client::class);
        $client->exists('test-key')->willReturn(true);
        $client->get('test-key')->willReturn('test-value');
        $client->ttl('test-key')->willReturn(5);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItems = $cacheItemPool->getItems(['test-key']);

        $this->assertInternalType('array', $cacheItems);
        $this->assertCount( 1, $cacheItems);
        foreach ($cacheItems as $cacheItem) {
            $this->assertTrue($cacheItem->isHit());
        }
    }

    /**
     * @dataProvider provideInvalidKey
     */
    public function testHasItemInvalidArgumentException($invalidKey)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItemPool->hasItem($invalidKey);
    }

    /**
     * @dataProvider provideBoolean
     */
    public function testHasItem($hasItemExpected)
    {
        $client = $this->prophesize(Client::class);
        $client->exists('test-key')->willReturn($hasItemExpected ? 1 : 0);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $hasItemActual = $cacheItemPool->hasItem('test-key');

        $this->assertInternalType('bool', $hasItemActual);
        $this->assertEquals($hasItemExpected, $hasItemActual);
    }

    /**
     * @dataProvider provideBoolean
     */
    public function testClear($isClearedExpected)
    {
        $client = $this->prophesize(Client::class);
        $client->flushdb()->shouldBeCalled();
        $client->dbsize()->willReturn($isClearedExpected ? 0 : 5);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $isClearedActual = $cacheItemPool->clear();

        $this->assertInternalType('bool', $isClearedActual);
        $this->assertEquals($isClearedExpected, $isClearedActual);
    }

    /**
     * @dataProvider provideInvalidKey
     */
    public function testDeleteItemInvalidArgumentException($invalidKey)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItemPool->deleteItem($invalidKey);
    }

    /**
     * @dataProvider provideBoolean
     */
    public function testDeleteItem($isDeletedExpected)
    {
        $client = $this->prophesize(Client::class);
        $client->del('test-key')->shouldBeCalled();
        $client->exists('test-key')->willReturn($isDeletedExpected ? 0 : 1);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $isDeletedActual = $cacheItemPool->deleteItem('test-key');

        $this->assertInternalType('bool', $isDeletedActual);
        $this->assertEquals($isDeletedExpected, $isDeletedActual);
    }

    /**
     * @dataProvider provideInvalidKey
     */
    public function testDeleteItemsInvalidArgumentException($invalidKey)
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItemPool->deleteItems([$invalidKey]);
    }

    /**
     * @dataProvider provideBoolean
     */
    public function testDeleteItems($isSuccessfullyDeletedExpected)
    {
        $client = $this->prophesize(Client::class);
        $client->del('test-key')->willReturn($isSuccessfullyDeletedExpected ? 1 : 0);
        $client->exists('test-key')->willReturn($isSuccessfullyDeletedExpected ? 0 : 1);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $isSuccessfullyDeletedActual = $cacheItemPool->deleteItems(['test-key']);

        $this->assertInternalType('bool', $isSuccessfullyDeletedActual);
        $this->assertEquals($isSuccessfullyDeletedExpected, $isSuccessfullyDeletedActual);
    }

    /**
     * @dataProvider provideBoolean
     */
    public function testSave($isSuccessfullySavedExpected)
    {
        $client = $this->prophesize(Client::class);
        $client->set('test-key', 'test-value')->willReturn($isSuccessfullySavedExpected ? 'OK' : null);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItem = RedisCacheItem::create('test-key', 'test-value');
        $isSuccessfullySavedActual = $cacheItemPool->save($cacheItem);

        $this->assertInternalType('bool', $isSuccessfullySavedActual);
        $this->assertEquals($isSuccessfullySavedExpected, $isSuccessfullySavedActual);
    }

    public function testSaveDeferred()
    {
        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItem = RedisCacheItem::create('test-key', 'test-value');

        $successfullySaved = $cacheItemPool->saveDeferred($cacheItem);
        $unsuccessfullySaved = $cacheItemPool->saveDeferred($cacheItem);

        $this->assertInternalType('bool', $successfullySaved);
        $this->assertTrue($successfullySaved);

        $this->assertInternalType('bool', $unsuccessfullySaved);
        $this->assertFalse($unsuccessfullySaved);
    }

    public function testCommitEmptyQueue()
    {
        $client = $this->prophesize(Client::class);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $successfullyCommitted = $cacheItemPool->commit();

        $this->assertInternalType('bool', $successfullyCommitted);
        $this->assertTrue($successfullyCommitted);
    }

    /**
     * @dataProvider provideBoolean
     */
    public function testCommit($successfullyCommittedExpected)
    {
        $client = $this->prophesize(Client::class);
        $client->set('test-key', 'test-value')->willReturn($successfullyCommittedExpected ? 'OK' : null);
        $cacheItemPool = new RedisCacheItemPool($client->reveal());

        $cacheItem = RedisCacheItem::create('test-key', 'test-value');
        $cacheItemPool->saveDeferred($cacheItem);
        $successfullyCommittedActual = $cacheItemPool->commit();

        $this->assertInternalType('bool', $successfullyCommittedActual);
        $this->assertEquals($successfullyCommittedExpected, $successfullyCommittedActual);
    }

    public function provideInvalidKey(): array
    {
        return [
            [null],
            [false],
            [-5],
            [0],
            [8],
            [['key' => 'value']],
            [new \stdClass()],
            [new \DateTime()],
        ];
    }

    public function provideBoolean(): array
    {
        return [
            [false],
            [true],
        ];
    }
}
