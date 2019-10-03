<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Exception;

use Exception;

class NotCorrectFormatException extends Exception
{
    public function __construct()
    {
        parent::__construct("Wrong Sypex Geo database format", 415);
    }
}