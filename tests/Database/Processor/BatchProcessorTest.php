<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\Processor\BatchProcessor;
use Yamilovs\SypexGeo\Database\Reader;

class BatchProcessorTest extends TestCase
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

    protected function createProcessor(): BatchProcessor
    {
        return new class($this->reader, $this->config) extends BatchProcessor {
            public function __set(string $name, $value)
            {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }

            public function getFirstByteIndexBlockRange(int $ip1n): array
            {
                return parent::getFirstByteIndexBlockRange($ip1n);
            }
        };
    }

    public function testFirstByteIndexBlock(): void
    {
        $processor = $this->createProcessor();
        $processor->byteIndexArray = [1, 2, 3, 4, 5];

        $result = $processor->getFirstByteIndexBlockRange(3);

        $this->assertIsArray($result);
        $this->assertEquals([3, 4], $result);
    }
}