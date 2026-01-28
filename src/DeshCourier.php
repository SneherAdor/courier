<?php

namespace Millat\DeshCourier;

use Millat\DeshCourier\Core\CourierManager;
use Millat\DeshCourier\Core\CourierRegistry;
use Millat\DeshCourier\Core\CourierResolver;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Support\ConfigHelper;

class DeshCourier
{
    private static ?CourierManager $manager = null;
    private static ?ConfigHelper $configHelper = null;
    
    private static function getManager(): CourierManager
    {
        if (self::$manager === null) {
            self::$manager = new CourierManager();
            self::loadDefaultCouriers();
        }
        
        return self::$manager;
    }
    
    private static function getConfigHelper(): ConfigHelper
    {
        if (self::$configHelper === null) {
            self::$configHelper = new ConfigHelper();
        }
        
        return self::$configHelper;
    }
    
    private static function loadDefaultCouriers(): void
    {
        $config = self::getConfigHelper();
        
        if ($config->has('pathao')) {
            self::registerPathao($config->get('pathao', []));
        }
    }
    
    private static function registerPathao(array $config): void
    {
        $manager = self::getManager();
        
        $manager->registerFactory('pathao', function () use ($config) {
            $pathaoConfig = new \Millat\DeshCourier\Drivers\Pathao\PathaoConfig($config);
            return new \Millat\DeshCourier\Drivers\Pathao\PathaoCourier($pathaoConfig);
        });
    }
    
    public static function use(string $name): CourierInterface
    {
        return self::getManager()->courier($name);
    }
    
    public static function register(CourierInterface $courier): void
    {
        self::getManager()->register($courier);
    }
    
    public static function registerFactory(string $name, callable $factory): void
    {
        self::getManager()->registerFactory($name, $factory);
    }
    
    public static function manager(): CourierManager
    {
        return self::getManager();
    }
    
    public static function available(): array
    {
        return self::getManager()->getAvailableCouriers();
    }
    
    public static function createShipment(string $courierName, \Millat\DeshCourier\DTO\Shipment|array $shipment): \Millat\DeshCourier\DTO\Shipment
    {
        return self::getManager()->createShipment($courierName, $shipment);
    }
    
    public static function track(string $courierName, string $trackingId): \Millat\DeshCourier\DTO\Tracking
    {
        return self::getManager()->track($courierName, $trackingId);
    }
    
    public static function estimateRate(string $courierName, \Millat\DeshCourier\DTO\Rate|array $rateRequest): \Millat\DeshCourier\DTO\Rate
    {
        return self::getManager()->estimateRate($courierName, $rateRequest);
    }

    public static function getStores(string $courierName, array $filters = []): array
    {
        return self::getManager()->getStores($courierName, $filters);
    }
}
