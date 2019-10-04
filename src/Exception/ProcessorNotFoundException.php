<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Exception;

use LogicException;

class ProcessorNotFoundException extends LogicException
{
    public function __construct(int $mode)
    {
        parent::__construct(sprintf('Processor that processed mode "%d" was not found', $mode), 404);
    }
}