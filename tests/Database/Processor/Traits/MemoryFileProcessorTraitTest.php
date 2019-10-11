<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\Processor\AbstractProcessor;
use Yamilovs\SypexGeo\Database\Processor\Traits\MemoryFileProcessorTrait;
use Yamilovs\SypexGeo\Database\Reader;

class MemoryFileProcessorTraitTest extends TestCase
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
            use MemoryFileProcessorTrait {
                getIndexBlockPosition as private getIndexBlockPositionTrait;
                getFirstByteIndexBlockRange as private getFirstByteIndexBlockRangeTrait;
            }

            protected function readRawData(int $packFormat, int $start, int $length): string {}
            protected function getDatabaseBlockPosition(string $ip, int $min, int $max): int {}

            public function __set(string $name, $value)
            {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }

            public function getIndexBlockPosition(string $ip, int $min, int $max): int
            {
                return $this->getIndexBlockPositionTrait($ip, $min, $max);
            }

            public function getFirstByteIndexBlockRange(int $ip1n): array
            {
                return $this->getFirstByteIndexBlockRangeTrait($ip1n);
            }
        };
    }

    public function testFirstByteIndexBlock(): void
    {
        $processor = $this->createProcessor();
        $processor->byteIndex = pack('NN', 100, 200);

        $result = $processor->getFirstByteIndexBlockRange(1);

        $this->assertIsArray($result);
        $this->assertEquals([100, 200], $result);
    }
}