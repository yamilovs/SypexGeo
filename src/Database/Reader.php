<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database;

use Yamilovs\SypexGeo\Database\Exception\{NotFoundException, PermissionDeniedException};

class Reader
{
    /** @var resource */
    protected $handle;

    public function __construct(string $databasePath)
    {
        $this->openDatabaseFile($databasePath);
    }

    protected function openDatabaseFile(string $databasePath): void
    {
        if (!file_exists($databasePath)) {
            throw new NotFoundException($databasePath);
        }

        if (false === $this->handle = @fopen($databasePath, 'rb')) {
            throw new PermissionDeniedException($databasePath);
        }
    }

    public function read(int $length): string
    {
        return fread($this->handle, $length);
    }

    public function seek(int $pos): int
    {
        return fseek($this->handle, $pos);
    }

    public function tell(): int
    {
        return ftell($this->handle);
    }
}