<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\Database\Processor\Traits\BatchFileProcessorTrait;
use Yamilovs\SypexGeo\Database\Processor\Traits\MemoryFileProcessorTrait;

class FileProcessor extends AbstractProcessor
{
    use BatchFileProcessorTrait;
    use MemoryFileProcessorTrait;
}