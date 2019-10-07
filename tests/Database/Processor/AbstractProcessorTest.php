<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\PackFormat;
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

    public function testUnpack(): void
    {
        $processor = $this->createProcessor();
        $this->config->packFormats = [
            PackFormat::COUNTRY => 'T:id/c2:iso/n2:lat/n2:lon/b:name_ru/b:name_en',
        ];

        [$id, $iso, $lat, $lon, $nameRu, $nameEn] = [5, 'RU', 111, 222, 'Имя', 'Name'];
        $pack = pack('Ca2ssa7a7', $id, $iso, $lat, $lon, $nameRu, $nameEn);

        $result = $processor->unpack(PackFormat::COUNTRY, $pack);

        $this->assertIsArray($result);
        $this->assertEquals($id, $result['id']);
        $this->assertEquals($iso, $result['iso']);
        $this->assertEquals((float)substr_replace((string)$lat, '.', 1, 0), $result['lat']);
        $this->assertEquals((float)substr_replace((string)$lon, '.', 1, 0), $result['lon']);
        $this->assertEquals($nameRu, $result['name_ru']);
        $this->assertEquals($nameEn, $result['name_en']);
    }
}