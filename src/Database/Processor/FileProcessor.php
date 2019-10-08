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

    protected function readData(int $start, int $max, int $packFormat): array
    {
        $raw = '';

        if ($start && $max) {
            $begin = (PackFormat::REGION === $packFormat)
                ? $this->regionBeginPos
                : $this->cityBeginPos;

            $this->reader->seek($begin + $start);
            $raw = $this->reader->read($max);
        }

        return $this->unpack($packFormat, $raw);
    }
}