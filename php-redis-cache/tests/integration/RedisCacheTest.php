<?php declare(strict_types=1);

namespace App\PhpRedis\Tests\Integration;

use App\PhpRedisCache\RedisCacheItemPool;
use Cache\IntegrationTests\CachePoolTest;
use Predis\Client;
use Predis\Connection\Parameters;

class RedisCacheTest extends CachePoolTest
{
    public function createCachePool()
    {
        $client = new Client(new Parameters([
            'host' => 'redis',
            'database' => '15',
        ]));

        return new RedisCacheItemPool($client);
    }
}
