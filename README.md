# SypexGeo

A new generation of [Sypex Geo library](https://sypexgeo.net/) 

# Installation 

You can install it through Composer:

```bash
$ composer require yamilovs/sypex-geo
```

# Basic Usage

```php
<?php

use Yamilovs\SypexGeo\Database\Mode;
use Yamilovs\SypexGeo\SypexGeo;

include('./vendor/autoload.php');

$sypexGeo = new SypexGeo(__DIR__.'/SxGeoCity.dat', Mode::FILE);

$city = $sypexGeo->getCity('5.189.19.230');

var_dump($city);
```