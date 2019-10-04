<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Exception;

use Exception;

class DatabaseCorruptException extends Exception
{
    public function __construct()
    {
        parent::__construct('Sypex Geo database file was corrupted', 422);
    }
}