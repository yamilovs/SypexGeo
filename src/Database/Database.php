<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo\Database;

use Yamilovs\SypexGeo\Database\Exception\UnsupportedModeException;

class Database
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Reader $reader, Config $config)
    {
        $this->reader = $reader;
        $this->config = $config;
    }

    public function init(int $mode = Mode::FILE): void
    {
        switch ($mode) {
            case Mode::FILE:
                break;
            case Mode::MEMORY:
                // todo this
                break;
            case Mode::BATCH:
                // todo this too
                break;
            default:
                throw new UnsupportedModeException($mode);
        }
    }
}