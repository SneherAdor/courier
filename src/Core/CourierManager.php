<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\Contracts\TrackingInterface;
use Millat\DeshCourier\Contracts\RateInterface;
use Millat\DeshCourier\Contracts\CodInterface;
use Millat\DeshCourier\Contracts\WebhookInterface;
use Millat\DeshCourier\Contracts\MetadataInterface;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Support\DtoNormalizer;

/**
 * Main manager class for interacting with courier services.
 * 
 * This is the primary entry point for the SDK.
 */
class CourierManager
{
    private CourierRegistry $registry;
    private CourierResolver $resolver;
    
    public function __construct(?CourierRegistry $registry = null, ?CourierResolver $resolver = null)
    {
        $this->registry = $registry ?? new CourierRegistry();
        $this->resolver = $resolver ?? new CourierResolver($this->registry);
    }
    
    /**
     * Get the registry instance.
     */
    public function getRegistry(): CourierRegistry
    {
        return $this->registry;
    }
    
    /**
     * Get the resolver instance.
     */
    public function getResolver(): CourierResolver
    {
        return $this->resolver;
    }
    
    /**
     * Register a courier driver.
     */
    public function register(CourierInterface $courier): self
    {
        $this->registry->register($courier);
        return $this;
    }
    
    /**
     * Register a courier factory (lazy loading).
     */
    public function registerFactory(string $name, callable $factory): self
    {
        $this->registry->registerFactory($name, $factory);
        return $this;
    }
    
    /**
     * Get a courier instance.
     */
    public function courier(string $name): CourierInterface
    {
        return $this->resolver->resolve($name);
    }
    
    /**
     * Create a shipment using the specified courier.
     */
    public function createShipment(string $courierName, Shipment $shipment): Shipment
    {
        $courier = $this->resolver->resolve($courierName);
        
        if (!$courier instanceof ShipmentInterface) {
            throw new \RuntimeException(
                "Courier '{$courierName}' does not support shipment creation."
            );
        }
        
        return $courier->createShipment($shipment);
    }
    
    /**
     * Track a shipment.
     */
    public function track(string $courierName, string $trackingId): Tracking
    {
        $courier = $this->resolver->resolve($courierName);
        
        if (!$courier instanceof TrackingInterface) {
            throw new \RuntimeException(
                "Courier '{$courierName}' does not support tracking."
            );
        }
        
        return $courier->track($trackingId);
    }
    
    /**
     * Estimate delivery rate.
     */
    public function estimateRate(string $courierName, Rate $rateRequest): Rate
    {
        $courier = $this->resolver->resolve($courierName);
        
        if (!$courier instanceof RateInterface) {
            throw new \RuntimeException(
                "Courier '{$courierName}' does not support rate estimation."
            );
        }
        
        return $courier->estimateRate($rateRequest);
    }
    
    /**
     * Get COD details.
     */
    public function getCodDetails(string $courierName, string $trackingId): Cod
    {
        $courier = $this->resolver->resolve($courierName);
        
        if (!$courier instanceof CodInterface) {
            throw new \RuntimeException(
                "Courier '{$courierName}' does not support COD operations."
            );
        }
        
        return $courier->getCodDetails($trackingId);
    }
    
    /**
     * Get all registered courier names.
     */
    public function getAvailableCouriers(): array
    {
        return $this->registry->getRegisteredNames();
    }
    
    /**
     * Find couriers that support specific capabilities.
     */
    public function findCouriersByCapabilities(array $capabilities): array
    {
        return $this->resolver->findByCapabilities($capabilities);
    }
}
