<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests\Database;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Config;
use Yamilovs\SypexGeo\Database\Database;
use Yamilovs\SypexGeo\Exception\ModeIsNotSupportedException;
use Yamilovs\SypexGeo\Database\Reader;

class DatabaseTest extends TestCase
{
    /**
     * @var MockObject|Reader
     */
    private $reader;

    /**
     * @var MockObject|Config
     */
    private $config;

    /**
     * @var Database
     */
    private $database;

    public function setUp(): void
    {
        $this->reader = $this->createMock(Reader::class);
        $this->config = $this->createMock(Config::class);

        $this->database = new Database($this->reader, $this->config);
    }

    public function testUnsupportedMode(): void
    {
        $this->expectException(ModeIsNotSupportedException::class);

        $this->database->init(-1);
    }
}