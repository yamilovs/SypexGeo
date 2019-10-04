<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database;

use org\bovigo\vfs\{vfsStream, vfsStreamDirectory, vfsStreamFile};
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Exception\DatabaseNotFoundException;
use Yamilovs\SypexGeo\Exception\DatabasePermissionDeniedException;
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
        $path = $this->filesystem->url().'/SxGeoCity.dat';

        $this->expectException(DatabaseNotFoundException::class);

        new Reader($path);
    }

    public function testDatabaseFileCanNotBeOpened(): void
    {
        $this->filesystem->addChild(new vfsStreamFile('SxGeoCity.dat', 0200));
        $path = $this->filesystem->url().'/SxGeoCity.dat';

        $this->expectException(DatabasePermissionDeniedException::class);

        new Reader($path);
    }

    public function testRead(): void
    {
        $reader = $this->prepareReaderWithFile();

        $this->assertEquals('I love', $reader->read(6));
        $this->assertEquals(' my cat', $reader->read(7));
    }

    public function testSeek(): void
    {
        $reader = $this->prepareReaderWithFile();

        $reader->seek(29);
        $this->assertEquals('fat', $reader->read(3));

        $reader->seek(20);
        $this->assertEquals('warm', $reader->read(4));

        $reader->seek(100);
        $this->assertEmpty($reader->read(1));
    }

    public function testTell(): void
    {
        $reader = $this->prepareReaderWithFile();

        $this->assertEquals(0, $reader->tell());

        $reader->seek(10);
        $this->assertEquals(10, $reader->tell());

        $reader->read(5);
        $this->assertEquals(15, $reader->tell());

        $reader->seek(100);
        $this->assertEquals(100, $reader->tell());
    }

    private function prepareReaderWithFile(string $content = "I love my cat. It's warm and fat"): Reader
    {
        $this->filesystem->addChild((new vfsStreamFile('test.dat'))->withContent($content));
        $path = $this->filesystem->url().'/test.dat';

        return new Reader($path);
    }
}