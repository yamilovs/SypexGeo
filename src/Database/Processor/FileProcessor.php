<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\Database\PackFormat;
use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;

class FileProcessor extends AbstractProcessor
{
    public function getCity(string $ip): City
    {
        // TODO: Implement getCity() method.
    }

    public function getCountry(string $ip): Country
    {
        // TODO: Implement getCountry() method.
    }

    protected function getRawData(int $packFormat, int $start, int $length): string
    {
        $begin = (PackFormat::REGION === $packFormat)
            ? $this->regionBeginPos
            : $this->cityBeginPos;

        $this->reader->seek($begin + $start);

        return $this->reader->read($length);
    }

    protected function searchIndex(string $ip, int $min, int $max): int
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
}