<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\Drivers\Pathao\PathaoConfig;
use Millat\DeshCourier\Drivers\Pathao\PathaoCourier;

DeshCourier::registerFactory('pathao', function () {
    return new PathaoCourier(new PathaoConfig([
        'client_id' => '7N1aMJQbWm',
        'client_secret' => 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39',
        'username' => 'test@pathao.com',
        'password' => 'lovePathao',
        'environment' => 'sandbox',
    ]));
});

dd(DeshCourier::getStores('pathao'));

$rate = DeshCourier::estimateRate('pathao', [
    'storeId' => 149718,
    'deliveryType' => 48,
    'weight' => 1.5,
    'toCity' => 1,
    'toZone' => 1,
]);

dd($rate);