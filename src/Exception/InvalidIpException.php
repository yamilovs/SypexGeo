<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Exception;

use InvalidArgumentException;

class InvalidIpException extends InvalidArgumentException
{
    public function __construct(string $ip)
    {
        parent::__construct(sprintf('"%s" does not contains a correct IP address', $ip), 406);
    }
}