<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Tests;

use PHPUnit\Framework\TestCase;
use Yamilovs\SypexGeo\Mode;

class ModeTest extends TestCase
{
    public function constantDataProvider(): array
    {
        return [
            [Mode::FILE],
            [Mode::MEMORY],
            [Mode::BATCH],
        ];
    }

    /**
     * @dataProvider constantDataProvider
     */
    public function testConstantExists(int $const): void
    {
        $this->assertContains($const, Mode::getModes());
    }
}