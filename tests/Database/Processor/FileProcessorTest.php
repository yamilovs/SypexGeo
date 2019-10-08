<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\PackFormat;
use Yamilovs\SypexGeo\Database\Processor\FileProcessor;
use Yamilovs\SypexGeo\Database\Reader;

class FileProcessorTest extends TestCase
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

    protected function createProcessor(): FileProcessor
    {
        return new class($this->reader, $this->config) extends FileProcessor {
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

    public function testReadZeroData(): void
    {
        $processor = $this->createProcessor();
        $this->config->packFormats = ['t:foo'];

        $result = $processor->readData(0, 0, 0);

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
    public function testReadRegionData(int $packFormat, int $regionPos, int $cityPos, int $expectedSeek): void
    {
        $processor = $this->createProcessor();
        $processor->regionBeginPos = $regionPos;
        $processor->cityBeginPos = $cityPos;
        $this->config->packFormats = [$packFormat => 't:foo'];
        $start = 3;
        $max = 1;

        $this->reader->expects($this->once())
            ->method('seek')
            ->with($expectedSeek + $start);
        $this->reader->expects($this->once())
            ->method('read')
            ->with($max)
            ->willReturn(pack('c', 100));

        $result = $processor->readData($start, $max, $packFormat);

        $this->assertEquals(100, $result['foo']);
    }
}