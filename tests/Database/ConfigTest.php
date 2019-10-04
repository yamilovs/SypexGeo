<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\{Config, PackFormat, Reader};
use Yamilovs\SypexGeo\Exception\{DatabaseCorruptException, DatabaseWrongFormatException};

class ConfigTest extends TestCase
{
    /**
     * @var MockObject|Reader
     */
    private $reader;

    public function setUp(): void
    {
        $this->reader = $this->createMock(Reader::class);
    }

    public function wrongDataBaseFormatProvider(): array
    {
        return [
            ['File does not contain correct identifier'],
            ['File has SxG header but not at beginning'],
        ];
    }

    /**
     * @dataProvider wrongDataBaseFormatProvider
     * @see Config::HEADER_LENGTH
     */
    public function testWrongDatabaseFormat(string $content): void
    {
        $this->reader->expects($this->once())
            ->method('read')
            ->with(40)
            ->willReturn($content);

        $this->expectException(DatabaseWrongFormatException::class);

        new Config($this->reader);
    }

    public function testDatabaseCorrupted(): void
    {
        $head = pack('CNCCCnnNCnnNNnNn', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

        $this->reader->expects($this->once())
            ->method('read')
            ->with(40)
            ->willReturn('SxG'.$head);

        $this->expectException(DatabaseCorruptException::class);

        new Config($this->reader);
    }

    public function testUnpackHead(): void
    {
        $version = 21;
        $timestamp = time();
        $parser = 3;
        $charset = 0;
        $byteIndexLength = 224;
        $mainIndexLength = 1775;
        $indexBlockCount = 2786;
        $databaseItems = 4946195;
        $idBlockLength = 3;
        $maxCitySize = 127;
        $maxRegionSize = 175;
        $maxCountrySize = 147;
        $citySize = 2625755;
        $regionSize = 109102;
        $countrySize = 9387;
        $packSize = 157;

        $head = pack(
            'CNCCCnnNCnnNNnNn',
            $version,
            $timestamp,
            $parser,
            $charset,
            $byteIndexLength,
            $mainIndexLength,
            $indexBlockCount,
            $databaseItems,
            $idBlockLength,
            $maxRegionSize,
            $maxCitySize,
            $regionSize,
            $citySize,
            $maxCountrySize,
            $countrySize,
            $packSize
        );

        $packFormats = [
            PackFormat::COUNTRY => 'T:id/c2:iso/n2:lat/n2:lon/b:name_ru/b:name_en',
            PackFormat::REGION => 'S:country_seek/M:id/b:name_ru/b:name_en/b:iso',
            PackFormat::CITY => 'M:region_seek/T:country_id/M:id/N5:lat/N5:lon/b:name_ru/b:name_en'
        ];

        $this->reader->expects($this->exactly(2)) // read head, then read pack formats
            ->method('read')
            ->withConsecutive([40], [$packSize])
            ->willReturnOnConsecutiveCalls(
                'SxG'.$head,
                implode("\0", $packFormats)
            );

        $config = new Config($this->reader);

        $this->assertEquals($version, $config->version);
        $this->assertEquals($timestamp, $config->timestamp);
        $this->assertEquals($parser, $config->parser);
        $this->assertEquals($charset, $config->charset);
        $this->assertEquals($byteIndexLength, $config->byteIndexLength);
        $this->assertEquals($mainIndexLength, $config->mainIndexLength);
        $this->assertEquals($indexBlockCount, $config->indexBlockCount);
        $this->assertEquals($databaseItems, $config->databaseItems);
        $this->assertEquals($idBlockLength, $config->idBlockLength);
        $this->assertEquals($maxCitySize, $config->maxCitySize);
        $this->assertEquals($maxRegionSize, $config->maxRegionSize);
        $this->assertEquals($maxCountrySize, $config->maxCountrySize);
        $this->assertEquals($citySize, $config->citySize);
        $this->assertEquals($regionSize, $config->regionSize);
        $this->assertEquals($countrySize, $config->countrySize);
        $this->assertEquals($packSize, $config->packSize);

        foreach ($packFormats as $key => $format) {
            $this->assertEquals($format, $config->packFormats[$key]);
        }
    }
}