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
            protected function readData(int $start, int $max, int $packFormat): array {}

            public function validateIp(string $ip): void
            {
                parent::validateIp($ip);
            }

            public function getRange(string $str, string $ip, int $min, int $max): int
            {
                parent::getRange($str, $ip, $min, $max);
            }

            public function unpack(int $packFormat, ?string $item = null): array
            {
                return parent::unpack($packFormat, $item);
            }
        };
    }

    public function testValidateWrongIpAddress(): void
    {
        $processor = $this->createProcessor();

        $this->expectException(InvalidIpException::class);
        $processor->validateIp('fat_cat');
    }

    public function unpackDataProvider(): array
    {
        return [
            ['t:foo', 'c', 1, 1],
            ['t:foo', 'c', -2, -2],
            ['T:foo', 'C', 3, 3],
            ['T:foo', 'C', -4, 2 ** 8 - 4],
            ['s:foo', 'l', 5, 5],
            ['s:foo', 'l', -6, -6],
            ['S:foo', 'L', 7, 7],
            ['S:foo', 'L', -8, 2 ** 16 - 8],
            ['n1:foo', 's', 123, 12.3],
            ['n5:foo', 's', 1, 0.00001],
            ['n2:foo', 's', 123, 1.23],
            ['n2:foo', 's', 123, 1.23],
            ['n0:foo', 's', 2 ** 16, 0],
            ['n0:foo', 's', -9, -9],
            ['n1:foo', 's', -123, -12.3],
            ['n1:foo', 's', -123, -12.3],
            ['N0:foo', 'l', 2 ** 16, 2 ** 16],
            ['N0:foo', 'l', -456, -456],
            ['N3:foo', 'l', -789, -0.789],
            ['m:foo', 'l', -5, -5],
            ['m:foo', 'l', 2 ** 23, -2 ** 23], // 23 bit + sign
            ['M:foo', 'L', 2 ** 24, 0], // 24 bit
            ['M:foo', 'L', -1, 2 ** 24 - 1],
            ['i:foo', 'l', -10, -10], // negative int
            ['i:foo', 'l', 2 ** 31, -2 ** 31], // 31 bit + sign
            ['I:foo', 'L', 2 ** 32, 0], // 32 bit
            ['I:foo', 'L', -1, 2 ** 32 - 1],
            ['f:foo', 'f', 7E-10, 7E-10],
            ['f:foo', 'f', 1.2e3, 1.2e3],
            ['d:foo', 'd', 7E-10, 7E-10],
            ['d:foo', 'd', 1.2e3, 1.2e3],
            ['d:foo', 'd', 1.234, 1.234],
            ['c2:iso', 'a2', 'RU', 'RU'],
            ['b:foo', 'a4', 'foo', 'foo'], // a4 = three charters + "\0"
            ['b:russian', 'a7', 'бар', 'бар'], // a7 = 6 (three Russian charters) + "\0"
            ['b:foo', null, null, ''],
            ['c:foo', null, null, ''],
            ['d:foo', null, null, 0],
        ];
    }

    /**
     * @dataProvider unpackDataProvider
     * @param $value mixed
     * @param $expected mixed
     */
    public function testUnpack(string $unpackFormat, ?string $packFormat, $value, $expected): void
    {
        $processor = $this->createProcessor();
        $this->config->packFormats = [$unpackFormat];
        $pack = ($value) ? pack($packFormat, $value) : null;
        $key = substr($unpackFormat, strpos($unpackFormat, ':') + 1);

        $result = $processor->unpack(0, $pack);

        $this->assertEquals($expected, $result[$key]);
    }
}