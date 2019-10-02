<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Exception;

use Exception;
use Throwable;

class NotFoundException extends Exception
{
    public function __construct(string $databasePath, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Sypex Geo database file was not found in path "%s"', $databasePath), $code, $previous);
    }
}