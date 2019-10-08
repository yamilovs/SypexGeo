<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\PackFormat;
use Yamilovs\SypexGeo\Database\Processor\FileProcessor;
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

            public function readData(int $start, int $max, int $packFormat): array
            {
                return parent::readData($start, $max, $packFormat);
            }
        };
    }

    public function readZeroDataProvider(): array
    {
        return [
            [0, 1],
            [0, 0],
            [1, 0],
        ];
    }

    /**
     * @dataProvider readZeroDataProvider
     */
    public function testReadZeroData(int $start, int $max): void
    {
        $processor = $this->createProcessor();
        $this->config->packFormats = ['t:foo'];

        $result = $processor->readData($start, $max, 0);

        $this->assertEquals(0, $result['foo']);
    }

    public function readDataProvider(): array
    {
        return [
            [PackFormat::COUNTRY, 5, 7, 7],
            [PackFormat::REGION, 5, 7, 5],
            [PackFormat::CITY, 5, 7, 7],
        ];
    }

    /**
     * @dataProvider readDataProvider
     */
    public function testReadRegionData(int $packFormat, int $regionInt, int $cityInt, int $expected): void
    {
        $processor = $this->createProcessor();
        $processor->regionDatabase = pack('xc', $regionInt);
        $processor->cityDatabase = pack('xc', $cityInt);
        $this->config->packFormats = [$packFormat => 't:foo'];

        $result = $processor->readData(1, 1, $packFormat);

        $this->assertEquals($expected, $result['foo']);
    }
}