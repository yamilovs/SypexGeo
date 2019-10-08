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

            public function getRawData(int $packFormat, int $start, int $length): string
            {
                return parent::getRawData($packFormat, $start, $length);
            }

            public function getFirstByteIndexBlockPosition(int $ip1n): array
            {
                return parent::getFirstByteIndexBlockPosition($ip1n);
            }
        };
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

        $processor->getRawData($packFormat, $start, $length);
    }

    public function testFirstByteIndexBlock(): void
    {
        $processor = $this->createProcessor();
        $processor->byteIndex = pack('NN', 100, 200);

        $result = $processor->getFirstByteIndexBlockPosition(1);

        $this->assertIsArray($result);
        $this->assertEquals([100, 200], $result);
    }
}