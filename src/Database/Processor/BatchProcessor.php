<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\Reader;

class BatchProcessor extends AbstractProcessor
{
    /**
     * @var array
     */
    protected $byteIndexArray;

    /**
     * @var array
     */
    protected $mainIndexArray;

    public function __construct(Reader $reader, Config $config)
    {
        parent::__construct($reader, $config);

        $this->byteIndexArray = array_values(unpack("N*", $this->byteIndex));
        $this->mainIndexArray = str_split($this->mainIndex, static::MAIN_INDEX_BYTES);

        unset ($this->byteIndex, $this->mainIndex);
    }

    public function getCity(string $ip): City
    {
        // TODO: Implement getCity() method.
    }

    public function getCountry(string $ip): Country
    {
        // TODO: Implement getCountry() method.
    }
}