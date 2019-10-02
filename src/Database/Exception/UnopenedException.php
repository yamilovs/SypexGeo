<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Exception;

use Exception;
use Throwable;

class UnopenedException extends Exception
{
    public function __construct(string $databasePath, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Cannot open Sypex Geo database file "%s"', $databasePath), $code, $previous);
    }
}