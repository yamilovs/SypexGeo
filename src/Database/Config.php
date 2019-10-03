<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database;

use Yamilovs\SypexGeo\Database\Exception\CorruptException;
use Yamilovs\SypexGeo\Database\Exception\WrongFormatException;

class Config
{
    protected const HEADER_LENGTH = 40;
    protected const IDENTIFIER = 'SxG';

    /**
     * Database file version 21 => 2.1
     *
     * @var int
     */
    public $version;

    /**
     * Date of database creation (in timestamp)
     *
     * @var int
     */
    public $timestamp;

    /**
     * Parsers type
     *  0 - Universal
     *  1 - SxGeo Country
     *  2 - SxGeo City
     *  11 - GeoIP Country
     *  12 - GeoIP City
     *  21 - ipgeobase
     *
     * @var int
     */
    public $parser;

    /**
     * Database charset
     *  0 - UTF-8
     *  1 - latin1
     *  2 - cp1251
     *
     * @var int
     */
    public $charset;

    /**
     * Element's count in index of first bytes
     * Up to 255
     *
     * @var int
     */
    public $byteIndexLength;

    /**
     * Element's count in main index
     * Up to 65 thousands
     *
     * @var int
     */
    public $mainIndexLength;

    /**
     * Blocks count in one index element
     * Up to 65 thousands
     *
     * @var int
     */
    public $indexBlockCount;

    /**
     * Ranges count
     * Up to 4 billions
     *
     * @var int
     */
    public $databaseItems;

    /**
     * Size of ID-block in bytes
     *  1 for countries
     *  3 for cities
     *
     * @var int
     */
    public $idBlockLength;

    /**
     * Maximum size of city record
     * Up to 64kb
     *
     * @var int
     */
    public $maxCitySize;

    /**
     * Maximum size of region record
     * Up to 64 kb
     *
     * @var int
     */
    public $maxRegionSize;

    /**
     * Maximum size of country record
     * Up to 64 kb
     *
     * @var int
     */
    public $maxCountrySize;

    /**
     * Size of city dictionary
     *
     * @var int
     */
    public $citySize;

    /**
     * Size of region dictionary
     *
     * @var int
     */
    public $regionSize;

    /**
     * Size of country dictionary
     *
     * @var int
     */
    public $countrySize;

    /**
     * Packaging format description for city / region / country
     *
     * @var int
     */
    public $packSize;

    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->unpackHead();
    }

    protected function unpackHead(): void
    {
        $head = $this->getHead();

        $pack = unpack(
            implode(
                '/',
                [
                    'Cversion',
                    'Ntimestamp',
                    'Cparser',
                    'Ccharset',
                    'Cbyte_index_length',
                    'nmain_index_length',
                    'nindex_block_count',
                    'Ndatabase_items',
                    'Cid_block_length',
                    'nmax_region_size',
                    'nmax_city_size',
                    'Nregion_size',
                    'Ncity_size',
                    'nmax_country_size',
                    'Ncountry_size',
                    'npack_size',
                ]
            ),
            substr($head, strlen(self::IDENTIFIER))
        );

        $this->version = $pack['version'];
        $this->timestamp = $pack['timestamp'];
        $this->parser = $pack['parser'];
        $this->charset = $pack['charset'];
        $this->byteIndexLength = $pack['byte_index_length'];
        $this->mainIndexLength = $pack['main_index_length'];
        $this->indexBlockCount = $pack['index_block_count'];
        $this->databaseItems = $pack['database_items'];
        $this->idBlockLength = $pack['id_block_length'];
        $this->maxCitySize = $pack['max_city_size'];
        $this->maxRegionSize = $pack['max_region_size'];
        $this->maxCountrySize = $pack['max_country_size'];
        $this->citySize = $pack['city_size'];
        $this->regionSize = $pack['region_size'];
        $this->countrySize = $pack['country_size'];
        $this->packSize = $pack['pack_size'];

        if (0 === $this->byteIndexLength * $this->mainIndexLength * $this->indexBlockCount * $this->databaseItems * $this->timestamp * $this->idBlockLength) {
            throw new CorruptException();
        }
    }

    protected function getHead(): string
    {
        $head = $this->reader->read(static::HEADER_LENGTH);

        if (strpos($head, static::IDENTIFIER) !== 0) {
            throw new WrongFormatException();
        }

        return $head;
    }
}