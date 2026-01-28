<?php

/**
 * Example: Using Arrays (Laravel-style, convenient)
 */

// Enable detailed error reporting for development (browser + CLI)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\Drivers\Pathao\PathaoConfig;
use Millat\DeshCourier\Drivers\Pathao\PathaoCourier;

// register the pathao courier as sandbox
DeshCourier::registerFactory('pathao', function () {
    return new PathaoCourier(new PathaoConfig([
        'client_id' => '7N1aMJQbWm',
        'client_secret' => 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39',
        'username' => 'test@pathao.com',
        'password' => 'lovePathao',
        'environment' => 'sandbox',
    ]));
});

$rate = DeshCourier::estimateRate('pathao', [
    'fromCity' => 'Dhaka',
    'toCity' => 'Chittagong',
    'codAmount' => 15000,
]);

dd($rate);