<?php

namespace CacheRedis;

use Predis\Client;
use Predis\ClientInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CacheItemPool implements CacheItemPoolInterface
{
    private $client;

    private $deferred;

    /**
     * CacheItemPool constructor.
     * @param $client
     */

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function getItem($key)
    {
        if (!is_string($key)) {
            throw new \CacheRedis\InvalidArgumentException("");
        }

        if(!$this->client->exists($key)) {
            return new CacheItem($key);
        }

        return new CacheItem($key, $this->client->get($key), true, $this->client->ttl($key));
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = array())
    {
        return array_map(function($key) { return $this->getItem($key); }, $keys);
    }

    /**
     * @inheritDoc
     */
    public function hasItem($key)
    {
        return $this->client->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->client->flushdb();
    }

    /**
     * @inheritDoc
     */
    public function deleteItem($key)
    {
        return $this->client->del([$key]);
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys)
    {
        return $this->client->del($keys);
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item)
    {
        if (!$item instanceof CacheItem) {
            throw new \RuntimeException("The CacheItem should be an instance");
        }

        $date = $item->getExpiration();

        if (null === $date) {
            $this->client->set($item->getKey(), $item->get());

            return true;
        }

        $ttl = (new \DateTime())->getTimestamp() - $date->getTimestamp();
        $this->client->set($item->getKey(), $item->get(), $ttl);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;
    }

    /**
     * @inheritDoc
     */
    public function commit()
    {
        /** @var CacheItem $value */
        foreach ($this->deferred as $value) {
            $this->save($value);

            unset($this->deferred[$value->getKey()]);
        }

        return true;
    }
}