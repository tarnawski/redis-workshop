<?php declare(strict_types=1);

namespace App\PhpRedisCache;

use Psr\Cache\CacheItemInterface;
use App\PhpRedisCache\Exception\InvalidArgumentException;

class RedisCacheItem implements CacheItemInterface
{
    private const DEFAULT_EXPIRATION_SECONDS = 60;

    /** @var string */
    private $key;

    /** @var mixed */
    private $value;

    /** @var bool */
    private $isHit;

    /** @var \DateTime|null */
    private $expiration;

    /**
     * @param string $key
     * @param bool $isHit
     * @param mixed $value
     */
    private function __construct(string $key, bool $isHit = false, $value = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
        $this->expiration = null;
    }

    /**
     * @param string $key
     * @return RedisCacheItem
     */
    public static function createEmpty(string $key): self
    {
        return new self($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return RedisCacheItem
     */
    public static function create(string $key, $value): self
    {
        return new self($key, true, $value);
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface|null $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     *
     * @throws InvalidArgumentException
     */
    public function expiresAt($expiration): self
    {
        if (null === $expiration) {
            $this->setDefaultExpiration();

            return $this;
        }

        if ($expiration instanceof \DateTimeInterface) {
            $this->expiration = $expiration;

            return $this;
        }

        throw new InvalidArgumentException(gettype($expiration), [
            \DateTimeInterface::class,
            'null',
        ]);
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval|null $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     *
     * @throws InvalidArgumentException
     */
    public function expiresAfter($time): self
    {
        if (null === $time) {
            $this->setDefaultExpiration();

            return $this;
        }

        if (is_int($time)) {
            $this->expiration = (new \DateTime())->modify((sprintf('+%s days', $time)));

            return $this;
        }

        if ($time instanceof \DateInterval) {
            $this->expiration = (new \DateTime())->add($time);

            return $this;
        }

        throw new InvalidArgumentException(gettype($time), [
            \DateTimeInterface::class,
            'int',
            'null',
        ]);
    }

    private function setDefaultExpiration(): void
    {
        $this->expiration = (new \DateTime())->modify((sprintf('+%s days', self::DEFAULT_EXPIRATION_SECONDS)));
    }
}
