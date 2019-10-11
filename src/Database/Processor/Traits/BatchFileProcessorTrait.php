<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor\Traits;

use Yamilovs\SypexGeo\Database\PackFormat;

trait BatchFileProcessorTrait
{
    protected function readRawData(int $packFormat, int $start, int $length): string
    {
        $begin = (PackFormat::REGION === $packFormat)
            ? $this->regionBeginPos
            : $this->cityBeginPos;

        $this->reader->seek($begin + $start);

        return $this->reader->read($length);
    }

    protected function getDatabaseBlockPosition(string $ip, int $min, int $max): int
    {
        $length = $max - $min;

        $this->reader->seek($this->databaseBeginPos + $min * $this->config->databaseBlockLength);

        return $this->searchDatabaseBlockPosition($this->reader->read($length * $this->config->databaseBlockLength), $ip, 0, $length);
    }
}