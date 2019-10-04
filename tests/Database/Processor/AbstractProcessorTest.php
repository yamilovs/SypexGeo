<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\Processor\AbstractProcessor;
use Yamilovs\SypexGeo\Database\Processor\ProcessorInterface;
use Yamilovs\SypexGeo\Database\Reader;
use Yamilovs\SypexGeo\Exception\InvalidIpException;

class AbstractProcessorTest extends TestCase
{
    /**
     * @var MockObject|Reader
     */
    private $reader;

    /**
     * @var MockObject|Config
     */
    private $config;

    public function setUp(): void
    {
        $this->reader = $this->createMock(Reader::class);
        $this->config = $this->createMock(Config::class);
    }

    protected function createProcessor(): ProcessorInterface
    {
        return new class($this->reader, $this->config) extends AbstractProcessor {
            public function getCity(string $ip): City {}
            public function getCountry(string $ip): Country {}

            public function validateIp(string $ip): void
            {
                parent::validateIp($ip);
            }

            public function searchPos(string $database, string $ip, int $min, int $max): int
            {
                parent::searchPos($database, $ip, $min, $max);
            }
        };
    }

    public function testValidateWrongIpAddress(): void
    {
        $processor = $this->createProcessor();

        $this->expectException(InvalidIpException::class);
        $processor->validateIp('fat_cat');
    }
}