<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\{City, Country};

interface ProcessorInterface
{
    public function getCity(string $ip): City;

    public function getCountry(string $ip): Country;
}