<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database\Processor;

use ArrayIterator;
use InfiniteIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\CountryIso;
use Yamilovs\SypexGeo\Database\Processor\AbstractProcessor;
use Yamilovs\SypexGeo\Database\Reader;
use Yamilovs\SypexGeo\Exception\InvalidIpException;
use Yamilovs\SypexGeo\Region;

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

    protected function createProcessor(): AbstractProcessor
    {
        return new class($this->reader, $this->config) extends AbstractProcessor {

            public $databaseBlockPosition = 1;
            public $indexBlockPosition = 1;
            public $firstByteBlockRange = [1, 1];

            /** @var InfiniteIterator */
            private $rawDataPack;

            public function setRawData(array $array): void
            {
                $this->rawDataPack = new InfiniteIterator(new ArrayIterator($array));
                $this->rawDataPack->rewind();
            }

            protected function getDatabaseBlockPosition(string $ip, int $min, int $max): int
            {
                return $this->databaseBlockPosition;
            }

            protected function getIndexBlockPosition(string $ip, int $min, int $max): int
            {
                return $this->indexBlockPosition;
            }

            protected function getFirstByteIndexBlockRange(int $ip1n): array
            {
                return $this->firstByteBlockRange;
            }

            protected function readRawData(int $packFormat, int $start, int $length): string
            {
                [$format, $args] = $this->rawDataPack->current();
                $this->rawDataPack->next();

                return pack($format, ...$args);
            }

            public function validateIp(string $ip): void
            {
                parent::validateIp($ip);
            }

            public function searchDatabaseBlockPosition(string $str, string $ip, int $min, int $max): int
            {
                parent::searchDatabaseBlockPosition($str, $ip, $min, $max);
            }

            public function unpack(int $packFormat, ?string $item = null): array
            {
                return parent::unpack($packFormat, $item);
            }

            public function readData(int $start, int $length, int $packFormat): array
            {
                return parent::readData($start, $length, $packFormat);
            }

            public function parseCity(string $ip, bool $withRelations = false): City
            {
                return parent::parseCity($ip, $withRelations);
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

    public function parseCityByNotAnIpThrowExceptionDataProvider(): array
    {
        return [
            [''],
            ['foo.bar'],
            ['200'],
            ['8.8.8.256'],
            ['10.0.0.0/32'],
        ];
    }

    /**
     * @dataProvider parseCityByNotAnIpThrowExceptionDataProvider
     */
    public function testParseCityByNotAnIpThrowException(string $string): void
    {
        $this->expectException(InvalidIpException::class);

        $this->createProcessor()->parseCity($string);
    }

    public function testParseCityByWrongEmailReturnEmptyCity(): void
    {
        $processor = $this->createProcessor();
        $this->config->byteIndexLength = 250;

        $ips = [
            '0.0.0.0',
            '10.0.0.1',
            '127.0.0.1',
            '255.0.0.1', // it's a correct ip address but it's larger than config's `byteIndexLength`
        ];

        foreach ($ips as $ip) {
            $this->assertEquals(new City(), $processor->parseCity($ip));
        }
    }

    public function testParseEmptyCity(): void
    {
        [$id, $iso, $lat, $lon, $ru, $en] = [1, 'RU', 100, 200, 'Россия', 'Russia'];

        $processor = $this->createProcessor();
        $processor->setRawData([
            ['Ca2ssa13a7', [$id, $iso, $lat, $lon, $ru, $en]],
        ]);
        $this->config->packFormats = ['T:id/c2:iso/n2:lat/n2:lon/b:name_ru/b:name_en'];
        $this->config->byteIndexLength = 255;
        $this->config->countrySize = 2;
        $this->config->maxCountrySize = 1;

        $city = $processor->parseCity('8.8.8.8');
        $country = $city->getCountry();

        $this->assertEmpty($city->getId());
        $this->assertEmpty($city->getLatitude());
        $this->assertEmpty($city->getLongitude());
        $this->assertEmpty($city->getNameRu());
        $this->assertEmpty($city->getNameEn());
        $this->assertEquals(new Region(), $city->getRegion());
        $this->assertSame($id, $country->getId());
        $this->assertSame($iso, $country->getIso());
        $this->assertEmpty($country->getLatitude());
        $this->assertEmpty($country->getLongitude());
        $this->assertEmpty($country->getNameRu());
        $this->assertEmpty($country->getNameEn());

        $full = $processor->parseCity('8.8.8.8', true);
        $fullCountry = $full->getCountry();

        $this->assertEquals(new Region(), $full->getRegion());
        $this->assertSame($id, $fullCountry->getId());
        $this->assertSame($iso, $fullCountry->getIso());
        $this->assertSame(round($lat/100, 1), $fullCountry->getLatitude());
        $this->assertSame(round($lon/100, 1), $fullCountry->getLongitude());
        $this->assertSame($ru, $fullCountry->getNameRu());
        $this->assertSame($en, $fullCountry->getNameEn());
    }

    public function testParseExistCity(): void
    {
        [$countryIso, $countryLat, $countryLon, $countryRu, $countryEn] = ['RU', 100, 200, 'Россия', 'Russia'];
        [$cityId, $cityLat, $cityLon, $cityRu, $cityEn] = [2, 300000, 400000, 'Москва', 'Moscow'];
        [$regionId, $regionIso, $regionRu, $regionEn] = [3, 'MSK', 'Область', 'Region'];
        $countryId = array_search($countryIso, CountryIso::ID_ISO, true);

        $processor = $this->createProcessor();
        $processor->setRawData([
            ['CxxCCxxlla13a7', [10, $countryId, $cityId, $cityLat, $cityLon, $cityRu, $cityEn]], // City pack
            ['CxxCCxxlla13a7', [10, $countryId, $cityId, $cityLat, $cityLon, $cityRu, $cityEn]], // City pack
            ['CxCxxa15a7a4', [1, $regionId, $regionRu, $regionEn, $regionIso]], // Region pack
            ['Ca2ssa13a7', [$countryId, $countryIso, $countryLat, $countryLon, $countryRu, $countryEn]], // Country pack
        ]);
        $this->config->packFormats = [
            'T:id/c2:iso/n2:lat/n2:lon/b:name_ru/b:name_en', // Country
            'S:country_seek/M:id/b:name_ru/b:name_en/b:iso', // Region
            'M:region_seek/T:country_id/M:id/N5:lat/N5:lon/b:name_ru/b:name_en', // City
        ];
        $this->config->byteIndexLength = 255;
        $this->config->countrySize = 1;
        $this->config->maxCitySize = 1;
        $this->config->maxRegionSize = 1;
        $this->config->maxCountrySize = 1;

        $city = $processor->parseCity('8.8.8.8');
        $country = $city->getCountry();

        $this->assertSame($cityId, $city->getId());
        $this->assertSame(round($cityLat/100000, 1), $city->getLatitude()); // N5 in pack city format
        $this->assertSame(round($cityLon/100000, 1), $city->getLongitude()); // N5 in pack city format
        $this->assertSame($cityRu, $city->getNameRu());
        $this->assertSame($cityEn, $city->getNameEn());
        $this->assertEquals(new Region(), $city->getRegion());
        $this->assertSame($countryId, $country->getId());
        $this->assertSame($countryIso, $country->getIso());
        $this->assertEmpty($country->getLatitude());
        $this->assertEmpty($country->getLongitude());
        $this->assertEmpty($country->getNameRu());
        $this->assertEmpty($country->getNameEn());

        $full = $processor->parseCity('8.8.8.8', true);
        $fullCountry = $full->getCountry();
        $fullRegion = $full->getRegion();

        $this->assertSame($regionId, $fullRegion->getId());
        $this->assertSame($regionIso, $fullRegion->getIso());
        $this->assertSame($regionRu, $fullRegion->getNameRu());
        $this->assertSame($regionEn, $fullRegion->getNameEn());
        $this->assertSame($countryId, $fullCountry->getId());
        $this->assertSame($countryIso, $fullCountry->getIso());
        $this->assertSame(round($countryLat/100, 1), $fullCountry->getLatitude());
        $this->assertSame(round($countryLon/100, 1), $fullCountry->getLongitude());
        $this->assertSame($countryRu, $fullCountry->getNameRu());
        $this->assertSame($countryEn, $fullCountry->getNameEn());
    }
}