<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests;

use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Database\Mode;
use Yamilovs\SypexGeo\Exception\ModeIsNotSupportedException;
use Yamilovs\SypexGeo\Exception\ProcessorNotFoundException;
use Yamilovs\SypexGeo\SypexGeo;

class SypexGeoTest extends TestCase
{
    public function testUnsupportedMode(): void
    {
        $this->expectException(ModeIsNotSupportedException::class);

        new SypexGeo('foo/database.dat', -1);
    }

    public function testProcessorNotFound(): void
    {
        $this->expectException(ProcessorNotFoundException::class);

        new class('foo/database.dat', Mode::BATCH) extends SypexGeo {
            protected const PROCESSOR_LIST = [];
        };
    }
}