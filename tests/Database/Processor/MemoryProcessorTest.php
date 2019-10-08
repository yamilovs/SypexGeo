<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\PackFormat;
use Yamilovs\SypexGeo\Database\Processor\MemoryProcessor;
use Yamilovs\SypexGeo\Database\Reader;

class MemoryProcessorTest extends TestCase
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

    protected function createProcessor(): MemoryProcessor
    {
        return new class($this->reader, $this->config) extends MemoryProcessor {
            public function __set(string $name, $value)
            {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }

            public function getRawData(int $packFormat, int $start, int $length): string
            {
                return parent::getRawData($packFormat, $start, $length);
            }
        };
    }

    public function readDataProvider(): array
    {
        return [
            [PackFormat::COUNTRY, 'foo', 'bar', 'bar'],
            [PackFormat::REGION, 'foo', 'bar', 'foo'],
            [PackFormat::CITY, 'foo', 'bar', 'bar'],
        ];
    }

    /**
     * @dataProvider readDataProvider
     */
    public function testGetRawData(int $packFormat, string $regionDatabase, string $cityDatabase, string $expected): void
    {
        $processor = $this->createProcessor();
        $processor->regionDatabase = $regionDatabase;
        $processor->cityDatabase = $cityDatabase;

        $result = $processor->getRawData($packFormat, 0, strlen($expected));

        $this->assertEquals($expected, $result);
    }
}