<?php declare(strict_types=1);

namespace App\PhpRedisCache\Tests\Unit;

use App\PhpRedisCache\RedisCacheItem;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

class RedisCacheItemTest extends TestCase
{
    /**
     * @dataProvider provideKey
     */
    public function testGetKey($expectedKey)
    {
        $cacheItem = RedisCacheItem::create($expectedKey, 'test-value');

        $key = $cacheItem->getKey();

        $this->assertEquals($expectedKey, $key);
        $this->assertInternalType('string', $key);
    }

    /**
     * @dataProvider provideValue
     */
    public function testGetForFound($expectedValue)
    {
        $cacheItem = RedisCacheItem::create('test-key', $expectedValue);

        $value = $cacheItem->get();
        $isHit = $cacheItem->isHit();

        $this->assertSame($expectedValue, $value);
        $this->assertTrue($isHit);
    }

    public function testGetForNotFound()
    {
        $cacheItemNotFound = RedisCacheItem::createEmpty('test-key');

        $value = $cacheItemNotFound->get();
        $isHit = $cacheItemNotFound->isHit();

        $this->assertNull($value);
        $this->assertFalse($isHit);
    }

    /**
     * @dataProvider provideValue
     */
    public function testSet($expectedValue)
    {
        $cacheItem = RedisCacheItem::createEmpty('test-key');
        $cacheItemActual = $cacheItem->set($expectedValue);

        $value = $cacheItem->get();

        $this->assertSame($expectedValue, $value);
        $this->assertSame($cacheItem, $cacheItemActual);
    }

    public function testExpiresAtInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $cacheItem = RedisCacheItem::createEmpty('test-key');
        $cacheItem->expiresAt('invalid-argument');
    }

    /**
     * @dataProvider provideExpirationDate
     */
    public function testExpiresAt($expiration)
    {
        $cacheItem = RedisCacheItem::createEmpty('test-key');
        $cacheItemActual = $cacheItem->expiresAt($expiration);

        $this->assertSame($cacheItem, $cacheItemActual);
    }

    public function testExpiresAfterInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $cacheItem = RedisCacheItem::createEmpty('test-key');
        $cacheItem->expiresAfter('invalid-argument');
    }

    /**
     * @dataProvider provideExpirationInterval
     */
    public function testExpiresAfter($expiration)
    {
        $cacheItem = RedisCacheItem::createEmpty('test-key');
        $cacheItemActual = $cacheItem->expiresAfter($expiration);

        $this->assertSame($cacheItem, $cacheItemActual);
    }

    public function provideKey(): array
    {
        return [
            [''],
            ['test-key'],
        ];
    }

    public function provideValue(): array
    {
        return [
            [null],
            [false],
            [''],
            ['test-value'],
            [-5],
            [0],
            [8],
            [['test-key' => 'test-value']],
            [new \stdClass()],
            [new \DateTime()],
        ];
    }

    public function provideExpirationDate(): array
    {
        return [
            [null],
            [new \DateTime()],
        ];
    }

    public function provideExpirationInterval(): array
    {
        return [
            [null],
            [5],
            [new \DateInterval('PT5S')],
        ];
    }
}
