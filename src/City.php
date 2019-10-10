<?php

declare(strict_types=1);

namespace Yamilovs\SypexGeo;

class City
{
    /**
     * @var int
     */
    protected $id;

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

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var Region
     */
    protected $region;

    public function __construct()
    {
        $this->country = new Country();
        $this->region = new Region();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): City
    {
        $this->id = $id;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): City
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): City
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getNameRu(): ?string
    {
        return $this->nameRu;
    }

    public function setNameRu(string $nameRu): City
    {
        $this->nameRu = $nameRu;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): City
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }
}