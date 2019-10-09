<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo;

class Country
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
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

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

    public function setId(int $id): Country
    {
        $this->id = $id;

        return $this;
    }

    public function getIso(): ?string
    {
        return $this->iso;
    }

    public function setIso(string $iso): Country
    {
        $this->iso = $iso;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): Country
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): Country
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getNameRu(): ?string
    {
        return $this->nameRu;
    }

    public function setNameRu(string $nameRu): Country
    {
        $this->nameRu = $nameRu;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): Country
    {
        $this->nameEn = $nameEn;

        return $this;
    }
}