<?php

namespace CacheRedis;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    private $key;

    private $value;

    private $isHit;

    private $expiresAt;

    /**
     * CacheItem constructor.
     * @param string $key
     * @param string $value
     * @param bool $isHit
     * @param null $expiresAt
     */
    public function __construct(string $key, string $value = '', bool $isHit = false, $expiresAt = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @inheritDoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function isHit()
    {
        return $this->isHit;
    }

    /**
     * @inheritDoc
     */
    public function set($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt($expiration)
    {
        $this->expiresAt = $expiration;
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter($time)
    {
        $this->expiresAt = new \DateTime(sprintf('+%d seconds', $time));
    }

    public function getExpiration()
    {
        return $this->expiresAt;
    }
}