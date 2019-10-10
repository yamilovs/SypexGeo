<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;
use Yamilovs\SypexGeo\Region;

interface ProcessorInterface
{
    public function getCity(string $ip, bool $full = false): City;

    public function getRegion(string $ip): Region;

    public function getCountry(string $ip): Country;
}