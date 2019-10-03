<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Exception;

use Exception;

class CorruptException extends Exception
{
    public function __construct()
    {
        parent::__construct('Sypex Geo database file was corrupted', 422);
    }
}