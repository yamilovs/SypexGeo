<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Exception;

use Exception;

class DatabaseWrongFormatException extends Exception
{
    public function __construct()
    {
        parent::__construct("Wrong Sypex Geo database format", 415);
    }
}