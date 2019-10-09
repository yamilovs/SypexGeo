<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo;

class Region
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $iso;

    /**
     * @var string
     */
    protected $nameRu;

    /**
     * @var string
     */
    protected $nameEn;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Region
    {
        $this->id = $id;

        return $this;
    }

    public function getIso(): ?string
    {
        return $this->iso;
    }

    public function setIso(string $iso): Region
    {
        $this->iso = $iso;

        return $this;
    }

    public function getNameRu(): ?string
    {
        return $this->nameRu;
    }

    public function setNameRu(string $nameRu): Region
    {
        $this->nameRu = $nameRu;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): Region
    {
        $this->nameEn = $nameEn;

        return $this;
    }
}