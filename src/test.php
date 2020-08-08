<?php

require_once('../vendor/autoload.php');
use Elbucho\AlpacaV2\Calendar;
use Elbucho\AlpacaV2\Clock;

$key      = 'PKPDSXCMHXH6CP77IHAB';
$secret   = 'ir44mrezkLkKr9wuhVVmOOT3D2PePOg9e/4/Y85D';
$endpoint = 'https://paper-api.alpaca.markets';

/*$calendar = new Calendar($key, $secret, $endpoint);

$start = new \DateTimeImmutable('January 21, 2019 00:00:00');
$end = new \DateTimeImmutable('February 12, 2019 23:59:59');

var_dump($calendar->getMarketDays($start, $end)); */

$clock = new Clock($key, $secret, $endpoint);

var_dump($clock->getClock());
