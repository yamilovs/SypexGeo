<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database\Processor;

use Yamilovs\SypexGeo\City;
use Yamilovs\SypexGeo\Country;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\CountryIso;
use Yamilovs\SypexGeo\Database\PackFormat;
use Yamilovs\SypexGeo\Database\Reader;
use Yamilovs\SypexGeo\Exception\InvalidIpException;
use Yamilovs\SypexGeo\Region;

abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * The index of the first bytes consists of 4-byte numbers containing an offset in the database for each first byte.
     */
    protected const FIRST_INDEX_BYTES = 4;

    /**
     * Size in bytes of the first ip addresses in each database fragments
     */
    protected const MAIN_INDEX_BYTES = 4;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Data of first bytes index
     *
     * @var string
     */
    protected $byteIndex;

    /**
     * Data of main index
     *
     * @var string
     */
    protected $mainIndex;

    /**
     * Position which indicates the beginning of the database data
     *
     * @var int
     */
    protected $databaseBeginPos;

    /**
     * Position which indicates the beginning of `regions` block in database
     *
     * @var int
     */
    protected $regionBeginPos;

    /**
     * Position which indicates the beginning of `cities` block in database
     *
     * @var int
     */
    protected $cityBeginPos;

    public function __construct(Reader $reader, Config $config)
    {
        $this->reader = $reader;
        $this->config = $config;

        $this->init();
    }

    protected function init(): void
    {
        $this->byteIndex = $this->reader->read(static::FIRST_INDEX_BYTES * $this->config->byteIndexLength);
        $this->mainIndex = $this->reader->read(static::MAIN_INDEX_BYTES * $this->config->mainIndexLength);
        $this->databaseBeginPos = $this->reader->tell();
        $this->regionBeginPos = $this->databaseBeginPos + $this->config->databaseItems * $this->config->databaseBlockLength;
        $this->cityBeginPos = $this->regionBeginPos + $this->config->regionSize;
    }

    protected function validateIp(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidIpException($ip);
        }
    }

    protected function getPackedIp(string $ip): string
    {
        return inet_pton($ip);
    }

    /**
     * Return position of data block
     *
     * @return int
     */
    protected function searchDatabaseBlockPosition(string $str, string $ip, int $min, int $max): int
    {
        $pIp = $this->getPackedIp($ip);

        if ($max - $min > 1) {
            $pIp = substr($pIp, 1);

            while ($max - $min > 8) {
                $offset = $min + $max >> 1;

                if ($pIp > substr($str, $offset * $this->config->databaseBlockLength, 3)) {
                    $min = $offset;
                } else {
                    $max = $offset;
                }
            }
            while ($pIp >= substr($str, $min * $this->config->databaseBlockLength, 3) && ++$min < $max) {}

        } else {
            $min++;
        }

        return hexdec(bin2hex(substr($str, $min * $this->config->databaseBlockLength - $this->config->idBlockLength, $this->config->idBlockLength)));
    }

    /**
     * Unpack data from custom pack format
     *
     * @see https://sypexgeo.net/ru/docs/sxgeo22/
     * @return array
     */
    protected function unpack(int $packFormat, ?string $item = null): array
    {
        $format = explode('/', $this->config->packFormats[$packFormat]);
        $unpacked = [];
        $start = 0;

        foreach ($format AS $part) {
            [$type, $name] = explode(':', $part);
            $type0 = $type[0];

            if (!$item) {
                $unpacked[$name] = in_array($type0, ['b','c']) ? '' : 0;
                continue;
            }

            switch ($type0) {
                case 't':
                case 'T':
                    $length = 1;
                    break;
                case 's':
                case 'n':
                case 'S':
                    $length = 2;
                    break;
                case 'm':
                case 'M':
                    $length = 3;
                    break;
                case 'i':
                case 'I':
                case 'f':
                case 'N':
                    $length = 4;
                    break;
                case 'd':
                    $length = 8;
                    break;
                case 'c':
                    $length = (int)substr($type, 1);
                    break;
                case 'b':
                    $length = strpos($item, "\0", $start) - $start;
                    break;
            }

            $pack = substr($item, $start, $length);

            switch ($type0) {
                case 't':
                    $value = unpack('c', $pack);
                    break;
                case 'T':
                    $value = unpack('C', $pack);
                    break;
                case 's':
                    $value = unpack('s', $pack);
                    break;
                case 'S':
                    $value = unpack('S', $pack);
                    break;
                case 'm':
                    $value = unpack('l', $pack.((ord($pack[2]) >> 7) ? "\xff" : "\0"));
                    break;
                case 'M':
                    $value = unpack('L', $pack."\0");
                    break;
                case 'i':
                    $value = unpack('l', $pack);
                    break;
                case 'I':
                    $value = unpack('L', $pack);
                    break;
                case 'f':
                    $value = unpack('f', $pack);
                    break;
                case 'd':
                    $value = unpack('d', $pack);
                    break;
                case 'n':
                    $value = current(unpack('s', $pack)) / 10 ** $type[1];
                    break;
                case 'N':
                    $value = current(unpack('l', $pack)) / 10 ** $type[1];
                    break;
                case 'c':
                    $value = rtrim($pack, ' ');
                    break;
                case 'b':
                    $value = $pack; $length++;
                    break;
            }

            $start += $length;
            $unpacked[$name] = is_array($value) ? current($value) : $value;
        }
        return $unpacked;
    }

    protected function readData(int $start, int $length, int $packFormat): array
    {
        $raw = '';

        if ($start && $length) {
            $raw = $this->readRawData($packFormat, $start, $length);
        }

        return $this->unpack($packFormat, $raw);
    }

    protected function getBlockPosition(string $ip): ?int
    {
        $ip1n = (int)$ip; // Get first ip byte

        if (0 === $ip1n || 10 === $ip1n || 127 === $ip1n ||$ip1n >= $this->config->byteIndexLength) {
            return null;
        }

        [$rangeMin, $rangeMax] = $this->getFirstByteIndexBlockRange($ip1n);

        if ($rangeMax - $rangeMin > $this->config->indexBlockCount) {

            // Looking for the block position in the main index
            $positionIndex = $this->getIndexBlockPosition(
                $ip,
                (int)floor($rangeMin / $this->config->indexBlockCount),
                (int)floor($rangeMax / $this->config->indexBlockCount) - 1
            );

            // We found the block number in which to look for IP, now we find the desired block in the database
            $min = $positionIndex > 0 ?
                $positionIndex * $this->config->indexBlockCount
                : 0;
            $max = $positionIndex > $this->config->mainIndexLength
                ? $this->config->databaseItems
                : ($positionIndex + 1) * $this->config->indexBlockCount;

            // Need to check that block did not go beyond the first byte's block
            if ($min < $rangeMin) {
                $min = $rangeMin;
            }
            if ($max > $rangeMax) {
                $max = $rangeMax;
            }

        } else {
            $min = $rangeMin;
            $max = $rangeMax;
        }

        // Find the desired range in the database
        return $this->getDatabaseBlockPosition($ip, $min, $max);
    }

    protected function parseCity(string $ip, bool $full = false): City
    {
        $this->validateIp($ip);

        $city = new City();
        $country = $city->getCountry();
        $region = $city->getRegion();

        if (null === $position = $this->getBlockPosition($ip)) {
            return $city;
        }

        if ($position < $this->config->countrySize) {
            $countryData = $this->readData($position, $this->config->maxCountrySize, PackFormat::COUNTRY);

            $country
                ->setId($countryData['id'])
                ->setIso($countryData['iso']);
        } else {
            $cityData = $this->readData($position, $this->config->maxCitySize, PackFormat::CITY);

            $city
                ->setId($cityData['id'])
                ->setLatitude($cityData['lat'])
                ->setLongitude($cityData['lon'])
                ->setNameRu($cityData['name_ru'])
                ->setNameEn($cityData['name_en']);

            $country
                ->setId($cityData['country_id'])
                ->setIso(CountryIso::ID_ISO[$cityData['country_id']]);
        }

        if ($full) {
            if (isset($cityData)) {
                $regionData = $this->readData($cityData['region_seek'], $this->config->maxRegionSize, PackFormat::REGION);

                $region
                    ->setId($regionData['id'])
                    ->setIso($regionData['iso'])
                    ->setNameRu($regionData['name_ru'])
                    ->setNameEn($regionData['name_en']);

                $countryData = $this->readData($regionData['country_seek'], $this->config->maxCountrySize, PackFormat::COUNTRY);
            }

            $country
                ->setLatitude($countryData['lat'])
                ->setLongitude($countryData['lon'])
                ->setNameRu($countryData['name_ru'])
                ->setNameEn($countryData['name_en']);
        }

        unset($cityData, $regionData, $countryData);

        return $city;
    }

    public function getCity(string $ip, bool $full = false): City
    {
        return $this->parseCity($ip, $full);
    }

    public function getRegion(string $ip): Region
    {
        return $this->parseCity($ip, true)->getRegion();
    }

    public function getCountry(string $ip): Country
    {
        return $this->parseCity($ip, true)->getCountry();
    }

    abstract protected function getDatabaseBlockPosition(string $ip, int $min, int $max): int;

    /**
     * Reading raw packed data from the database
     */
    abstract protected function readRawData(int $packFormat, int $start, int $length): string;

    /**
     * Return a block position in main index
     */
    abstract protected function getIndexBlockPosition(string $ip, int $min, int $max): int;

    /**
     * Find the data block in the first byte index
     *
     * @param int $ip1n first ip byte
     *
     * @return array[min, max] block position
     */
    abstract protected function getFirstByteIndexBlockRange(int $ip1n): array;
}
