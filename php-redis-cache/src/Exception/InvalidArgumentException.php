<?php declare(strict_types=1);

namespace App\PhpRedisCache\Exception;

use \Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;
use Throwable;

class InvalidArgumentException extends CacheException implements PsrInvalidArgumentException
{
    public function __construct(string $provided, array $expected, $code = 0, $previous = null)
    {
        parent::__construct(sprintf(
            'Invalid type "%s", expected "%s".',
            $provided,
            implode('" or "', $expected)
        ), $code, $previous);
    }
}
