<?php

require_once('../vendor/autoload.php');

use Elbucho\AlpacaV2\Client;
use Elbucho\AlpacaV2\API\Order;

$key      = 'PKPDSXCMHXH6CP77IHAB';
$secret   = 'ir44mrezkLkKr9wuhVVmOOT3D2PePOg9e/4/Y85D';
$endpoint = 'https://paper-api.alpaca.markets';

$client = new Client($key, $secret, $endpoint);

/*$calendar = new Calendar($key, $secret, $endpoint);

$start = new \DateTimeImmutable('January 21, 2019 00:00:00');
$end = new \DateTimeImmutable('February 12, 2019 23:59:59');

var_dump($calendar->getMarketDays($start, $end));

$clock = new Clock($key, $secret, $endpoint);

var_dump($clock->getClock());

$account = new Account($key, $secret, $endpoint);

var_dump($account->getValue());die; */

$order = new Order();
$order->{'Symbol'} = 'CBL';
$order->{'Side'} = Order::SIDE_BUY;
$order->{'Quantity'} = 1;
$order->{'Type'} = Order::TYPE_MARKET;
$order->{'TimeInForce'} = Order::TIF_DAY;

var_dump($client->orders()->placeOrder($order), $order);