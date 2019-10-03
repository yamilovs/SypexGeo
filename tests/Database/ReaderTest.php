<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database;

use org\bovigo\vfs\{vfsStream, vfsStreamDirectory, vfsStreamFile};
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Exception\NotFoundException;
use Yamilovs\SypexGeo\Database\Exception\PermissionDeniedException;
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

        $this->expectException(NotFoundException::class);

        new Reader($path);
    }

    public function testDatabaseFileCanNotBeOpened(): void
    {
        $this->filesystem->addChild(new vfsStreamFile('SxGeoCity.dat', 0200));
        $path = $this->filesystem->url().'/SxGeoCity.dat';

        $this->expectException(PermissionDeniedException::class);

        new Reader($path);
    }

    public function testRead(): void
    {
        $content = "I love my cat. It's warm and fat";
        $this->filesystem->addChild((new vfsStreamFile('test.dat'))->withContent($content));
        $path = $this->filesystem->url().'/test.dat';

        $reader = new Reader($path);

        $this->assertEquals(substr($content, 0, 6), $reader->read(6));
        $this->assertEquals(substr($content, 6, 7), $reader->read(7));
    }
}