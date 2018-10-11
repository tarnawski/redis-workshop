<?php

namespace CacheRedis;

use \Exception;
use Psr\Cache\CacheException;

class InvalidArgumentException extends Exception implements CacheException
{

}