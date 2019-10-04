<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Exception;

use Exception;

class DatabaseNotFoundException extends Exception
{
    public function __construct(string $databasePath)
    {
        parent::__construct(sprintf('Sypex Geo database file was not found in path "%s"', $databasePath), 404);
    }
}