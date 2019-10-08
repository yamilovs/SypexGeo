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
}