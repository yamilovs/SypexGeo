<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Exception;

use LogicException;
use Yamilovs\SypexGeo\Database\Mode;

class ModeNotSupportedException extends LogicException
{
    public function __construct(int $mode)
    {
        $supported = implode('; ', array_map(function (string $key, int $val): string {
            return sprintf('%s: %d', $key, $val);
        }, array_keys(Mode::getModes()), Mode::getModes()));

        parent::__construct(sprintf('Mode "%d" is not supported. Supported modes: %s', $mode, $supported), 406);
    }
}