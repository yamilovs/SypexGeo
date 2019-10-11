<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor\Traits;

trait MemoryFileProcessorTrait
{
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
}