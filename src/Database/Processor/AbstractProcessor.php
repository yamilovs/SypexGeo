<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\Exception\InvalidIpException;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected function validateIp(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidIpException($ip);
        }
    }

    protected function getPackedIp(string $ip): string
    {
        return inet_pton($ip);
    }


}