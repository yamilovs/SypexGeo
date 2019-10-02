<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database;

use org\bovigo\vfs\{vfsStream, vfsStreamDirectory, vfsStreamFile};
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Exception\NotFoundException;
use Yamilovs\SypexGeo\Database\Exception\UnopenedException;
use Yamilovs\SypexGeo\Database\Reader;

class ReaderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $filesystem;

    public function setUp(): void
    {
        $this->filesystem = vfsStream::setup();
    }

    public function testDatabaseFileNotFound(): void
    {
        $path = $this->filesystem->url()."/SxGeoCity.dat";

        $this->expectException(NotFoundException::class);

        new Reader($path);
    }

    public function testDatabaseFileCanNotBeOpened(): void
    {
        $this->filesystem->addChild(new vfsStreamFile('SxGeoCity.dat', 0200));

        $path = $this->filesystem->url()."/SxGeoCity.dat";

        $this->expectException(UnopenedException::class);

        new Reader($path);
    }
}