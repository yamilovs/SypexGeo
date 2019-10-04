<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo;

use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Exception\ModeIsNotSupportedException;
use Yamilovs\SypexGeo\Exception\ProcessorNotFoundException;
use Yamilovs\SypexGeo\Database\Mode;
use Yamilovs\SypexGeo\Database\Processor\BatchProcessor;
use Yamilovs\SypexGeo\Database\Processor\FileProcessor;
use Yamilovs\SypexGeo\Database\Processor\MemoryProcessor;
use Yamilovs\SypexGeo\Database\Processor\ProcessorInterface;
use Yamilovs\SypexGeo\Database\Reader;

class SypexGeo
{
    protected const PROCESSOR_LIST = [
        Mode::FILE => FileProcessor::class,
        Mode::MEMORY => MemoryProcessor::class,
        Mode::BATCH => BatchProcessor::class,
    ];

    /**
     * @var ProcessorInterface
     */
    protected $processor;

    public function __construct(string $database, int $mode = Mode::FILE)
    {
        $this->setUpProcessor($database, $mode);
    }

    public function getCity(string $ip): City
    {
        $this->processor->getCity($ip);
    }

    public function getCountry(string $ip): Country
    {
        $this->processor->getCountry($ip);
    }

    protected function setUpProcessor(string $database, int $mode = Mode::FILE): void
    {
        if (!in_array($mode, Mode::getModes(), true)) {
            throw new ModeIsNotSupportedException($mode);
        }

        if (!array_key_exists($mode, static::PROCESSOR_LIST)) {
            throw new ProcessorNotFoundException($mode);
        }

        $reader = new Reader($database);
        $config = new Config($reader);
        $class = static::PROCESSOR_LIST[$mode];

        $this->processor = new $class($reader, $config);
    }
}