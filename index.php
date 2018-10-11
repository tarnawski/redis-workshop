<?php

require_once 'vendor/autoload.php';




$client = new \Predis\Client([
    'host' => 'redis'
]);

$cache = new \CacheRedis\CacheItemPool($client);


$item = new \CacheRedis\CacheItem('test', 'test_value');

$cache->save($item);


var_dump($cache->getItem('test'));