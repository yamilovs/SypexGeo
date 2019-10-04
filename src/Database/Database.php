<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database;

use Yamilovs\SypexGeo\Exception\ModeIsNotSupportedException;

class Database
{
    /**
     * The index of the first bytes consists of 4-byte numbers containing an offset in the database for each first byte.
     */
    protected const FIRST_INDEX_BYTES = 4;

    /**
     * Size in bytes of the first ip addresses in each database fragments
     */
    protected const MAIN_INDEX_BYTES = 4;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Main database packed data
     *
     * @var array
     */
    protected $data;

    /**
     * Data of first bytes index
     *
     * @var string
     */
    protected $byteIndex;

    /**
     * Data of main index
     *
     * @var string
     */
    protected $mainIndex;

    /**
     * Position which indicates the beginning of the database data
     *
     * @var int
     */
    protected $databaseBeginPos;

    /**
     * Position which indicates the beginning of `regions` block in database
     *
     * @var int
     */
    protected $regionBeginPos;

    /**
     * Position which indicates the beginning of `cities` block in database
     *
     * @var int
     */
    protected $cityBeginPos;

    public function __construct(Reader $reader, Config $config)
    {
        $this->reader = $reader;
        $this->config = $config;
    }

    public function init(int $mode = Mode::FILE): void
    {
        if (!in_array($mode, Mode::getModes(), true)) {
            throw new ModeIsNotSupportedException($mode);
        }

        $this->data = $this->config->packSize
            ? explode("\0", $this->reader->read($this->config->packSize))
            : [];
        $this->byteIndex = $this->reader->read(static::FIRST_INDEX_BYTES * $this->config->byteIndexLength);
        $this->mainIndex = $this->reader->read(static::MAIN_INDEX_BYTES * $this->config->mainIndexLength);
        $this->databaseBeginPos = $this->reader->tell();
        $this->regionBeginPos = $this->databaseBeginPos + $this->config->databaseItems * $this->config->databaseBlockLength;
        $this->cityBeginPos = $this->regionBeginPos + $this->config->regionSize;

        switch ($mode) {
            case Mode::FILE:
                break;
            case Mode::MEMORY:
                // todo this
                break;
            case Mode::BATCH:
                // todo this too
                break;
        }
    }
}