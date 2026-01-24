<?php

namespace Millat\DeshCourier;

use Millat\DeshCourier\Core\CourierManager;
use Millat\DeshCourier\Core\CourierRegistry;
use Millat\DeshCourier\Core\CourierResolver;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Support\ConfigHelper;

/**
 * Main facade for Desh Courier SDK.
 * 
 * This is the primary entry point for using the SDK.
 * 
 * Usage:
 *   DeshCourier::use('pathao')->createShipment($shipment);
 *   DeshCourier::track('pathao', 'TRACK123');
 */
class DeshCourier
{
    private static ?CourierManager $manager = null;
    private static ?ConfigHelper $configHelper = null;
    
    /**
     * Get or create the courier manager instance.
     */
    private static function getManager(): CourierManager
    {
        if (self::$manager === null) {
            self::$manager = new CourierManager();
            self::loadDefaultCouriers();
        }
        
        return self::$manager;
    }
    
    /**
     * Get configuration helper.
     */
    private static function getConfigHelper(): ConfigHelper
    {
        if (self::$configHelper === null) {
            self::$configHelper = new ConfigHelper();
        }
        
        return self::$configHelper;
    }
    
    /**
     * Load default courier drivers from configuration.
     */
    private static function loadDefaultCouriers(): void
    {
        $config = self::getConfigHelper();
        
        // Load Pathao if configured
        if ($config->has('pathao')) {
            self::registerPathao($config->get('pathao', []));
        }
        
        // Add more couriers here as they're implemented
    }
    
    /**
     * Register Pathao courier.
     */
    private static function registerPathao(array $config): void
    {
        $manager = self::getManager();
        
        $manager->registerFactory('pathao', function () use ($config) {
            $pathaoConfig = new \Millat\DeshCourier\Drivers\Pathao\PathaoConfig($config);
            return new \Millat\DeshCourier\Drivers\Pathao\PathaoCourier($pathaoConfig);
        });
    }
    
    /**
     * Get a courier instance by name.
     * 
     * @param string $name
     * @return CourierInterface
     */
    public static function use(string $name): CourierInterface
    {
        return self::getManager()->courier($name);
    }
    
    /**
     * Register a custom courier driver.
     * 
     * @param CourierInterface $courier
     * @return void
     */
    public static function register(CourierInterface $courier): void
    {
        self::getManager()->register($courier);
    }
    
    /**
     * Register a courier factory (lazy loading).
     * 
     * @param string $name
     * @param callable $factory
     * @return void
     */
    public static function registerFactory(string $name, callable $factory): void
    {
        self::getManager()->registerFactory($name, $factory);
    }
    
    /**
     * Get the courier manager instance (for advanced usage).
     * 
     * @return CourierManager
     */
    public static function manager(): CourierManager
    {
        return self::getManager();
    }
    
    /**
     * Get all available courier names.
     * 
     * @return array<string>
     */
    public static function available(): array
    {
        return self::getManager()->getAvailableCouriers();
    }
    
    /**
     * Create a shipment.
     * 
     * @param string $courierName
     * @param \Millat\DeshCourier\DTO\Shipment|array<string, mixed> $shipment Shipment DTO or array
     * @return \Millat\DeshCourier\DTO\Shipment
     */
    public static function createShipment(string $courierName, \Millat\DeshCourier\DTO\Shipment|array $shipment): \Millat\DeshCourier\DTO\Shipment
    {
        return self::getManager()->createShipment($courierName, $shipment);
    }
    
    /**
     * Track a shipment.
     * 
     * @param string $courierName
     * @param string $trackingId
     * @return \Millat\DeshCourier\DTO\Tracking
     */
    public static function track(string $courierName, string $trackingId): \Millat\DeshCourier\DTO\Tracking
    {
        return self::getManager()->track($courierName, $trackingId);
    }
    
    /**
     * Estimate delivery rate.
     * 
     * @param string $courierName
     * @param \Millat\DeshCourier\DTO\Rate|array<string, mixed> $rateRequest Rate DTO or array
     * @return \Millat\DeshCourier\DTO\Rate
     */
    public static function estimateRate(string $courierName, \Millat\DeshCourier\DTO\Rate|array $rateRequest): \Millat\DeshCourier\DTO\Rate
    {
        return self::getManager()->estimateRate($courierName, $rateRequest);
    }
}
