<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\Database\PackFormat;

class FileProcessor extends AbstractProcessor
{
    protected function readRawData(int $packFormat, int $start, int $length): string
    {
        $begin = (PackFormat::REGION === $packFormat)
            ? $this->regionBeginPos
            : $this->cityBeginPos;

        $this->reader->seek($begin + $start);

        return $this->reader->read($length);
    }

    protected function getIndexBlockPosition(string $ip, int $min, int $max): int
    {
        $packedIp = $this->getPackedIp($ip);

        while ($max - $min > 8) {
            $offset = ($min + $max) >> 1;
            if ($packedIp > substr($this->mainIndex, $offset * static::MAIN_INDEX_BYTES, static::MAIN_INDEX_BYTES)) {
                $min = $offset;
            } else {
                $max = $offset;
            }
        }

        while ($packedIp > substr($this->mainIndex, $min * static::MAIN_INDEX_BYTES, static::MAIN_INDEX_BYTES) && $min++ < $max) {}

        return $min;
    }

    protected function getFirstByteIndexBlockRange(int $ip1n): array
    {
        return array_values(
            unpack(
                "N2",
                substr($this->byteIndex, ($ip1n - 1) * static::FIRST_INDEX_BYTES, static::FIRST_INDEX_BYTES * 2)
            )
        );
    }

    protected function getDatabaseBlockPosition(string $ip, int $min, int $max): int
    {
        $length = $max - $min;

        $this->reader->seek($this->databaseBeginPos + $min * $this->config->databaseBlockLength);

        return $this->searchDatabaseBlockPosition($this->reader->read($length * $this->config->databaseBlockLength), $ip, 0, $length);
    }
}