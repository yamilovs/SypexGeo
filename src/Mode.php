<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo;

use ReflectionClass;

class Mode
{
    /**
     * Work with the base file, default mode
     *
     * @var int
     */
    public const FILE = 0;

    /**
     * Batch processing, increases the speed when processing multiple IP at a time
     *
     * @var int
     */
    public const MEMORY = 1;

    /**
     * Database cached in memory, still increases batch processing speed, but requires more memory to load the entire database into memory
     *
     * @var int
     */
    public const BATCH = 2;

    public static function getModes(): array
    {
        return (new ReflectionClass(static::class))->getConstants();
    }
}