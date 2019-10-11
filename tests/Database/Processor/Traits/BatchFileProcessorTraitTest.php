<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\PackFormat;
use Yamilovs\SypexGeo\Database\Processor\AbstractProcessor;
use Yamilovs\SypexGeo\Database\Processor\Traits\BatchFileProcessorTrait;
use Yamilovs\SypexGeo\Database\Reader;

class BatchFileProcessorTraitTest extends TestCase
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

    protected function createProcessor()
    {
        return new class($this->reader, $this->config) extends AbstractProcessor
        {
            use BatchFileProcessorTrait {
                readRawData as private readRawDataTrait;
                getDatabaseBlockPosition as private getDatabaseBlockPositionTrait;
            }

            protected function getFirstByteIndexBlockRange(int $ip1n): array {}
            protected function getIndexBlockPosition(string $ip, int $min, int $max): int {}

            public function __set(string $name, $value)
            {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }

            public function readRawData(int $packFormat, int $start, int $length): string
            {
                return $this->readRawDataTrait($packFormat, $start, $length);
            }

            public function getDatabaseBlockPosition(string $ip, int $min, int $max): int
            {
                return $this->getDatabaseBlockPositionTrait($ip, $min, $max);
            }
        };
    }

    public function getRawDataProvider(): array
    {
        return [
            [PackFormat::COUNTRY, 5, 7, 7],
            [PackFormat::REGION, 5, 7, 5],
            [PackFormat::CITY, 5, 7, 7],
        ];
    }

    /**
     * @dataProvider getRawDataProvider
     */
    public function testGetRawData(int $packFormat, int $regionPos, int $cityPos, int $expectedSeek): void
    {
        $processor = $this->createProcessor();
        $processor->regionBeginPos = $regionPos;
        $processor->cityBeginPos = $cityPos;
        $this->config->packFormats = [$packFormat => 't:foo'];
        $start = 3;
        $length = 1;

        $this->reader->expects($this->once())
            ->method('seek')
            ->with($expectedSeek + $start);
        $this->reader->expects($this->once())
            ->method('read')
            ->with($length)
            ->willReturn(pack('c', 100));

        $processor->readRawData($packFormat, $start, $length);
    }
}