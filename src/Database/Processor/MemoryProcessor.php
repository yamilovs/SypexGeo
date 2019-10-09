<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\PackFormat;
use Yamilovs\SypexGeo\Database\Reader;

class MemoryProcessor extends AbstractProcessor
{
    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $regionDatabase;

    /**
     * @var string
     */
    protected $cityDatabase;

    public function __construct(Reader $reader, Config $config)
    {
        parent::__construct($reader, $config);

        $this->database = $this->reader->read($this->config->databaseItems * $this->config->databaseBlockLength);
        $this->regionDatabase = $this->config->regionSize > 0
            ? $this->reader->read($this->config->regionSize)
            : '';
        $this->cityDatabase = $this->config->citySize > 0
            ? $this->reader->read($this->config->citySize)
            : '';
    }

    public function getCity(string $ip): City
    {
        // TODO: Implement getCity() method.
    }

    public function getCountry(string $ip): Country
    {
        // TODO: Implement getCountry() method.
    }

    protected function readRawData(int $packFormat, int $start, int $length): string
    {
        $db = PackFormat::REGION === $packFormat ? $this->regionDatabase : $this->cityDatabase;

        return substr($db, $start, $length);
    }

    protected function getIndexBlockPosition(string $ip, int $min, int $max): int
    {
        // TODO: Same as FileProcessor::getIndexBlockPosition()
    }

    protected function getFirstByteIndexBlockRange(int $ip1n): array
    {
        // TODO: Same as FileProcessor::getFirstByteIndexBlockRange()
    }

    protected function getDatabaseBlockPosition(string $ip, int $min, int $max): int
    {
        return $this->searchDatabaseBlockPosition($this->database, $ip, $min, $max);
    }
}