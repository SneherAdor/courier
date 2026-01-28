<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\Support\DtoNormalizer;
use Millat\DeshCourier\Contracts\CodInterface;
use Millat\DeshCourier\Contracts\RateInterface;
use Millat\DeshCourier\Contracts\StoreInterface;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\WebhookInterface;
use Millat\DeshCourier\Contracts\MetadataInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\Contracts\TrackingInterface;

class CourierManager
{
    private CourierRegistry $registry;
    private CourierResolver $resolver;
    
    public function __construct(?CourierRegistry $registry = null, ?CourierResolver $resolver = null)
    {
        $this->registry = $registry ?? new CourierRegistry();
        $this->resolver = $resolver ?? new CourierResolver($this->registry);
    }
    
    public function getRegistry(): CourierRegistry
    {
        return $this->registry;
    }
    
    public function getResolver(): CourierResolver
    {
        return $this->resolver;
    }
    
    public function register(CourierInterface $courier): self
    {
        $this->registry->register($courier);
        return $this;
    }
    
    public function registerFactory(string $name, callable $factory): self
    {
        $this->registry->registerFactory($name, $factory);
        return $this;
    }
    
    public function courier(string $name): CourierInterface
    {
        return $this->resolver->resolve($name);
    }
    
    public function createShipment(string $courierName, Shipment|array $shipment): Shipment
    {
        $courier = $this->resolver->resolve($courierName);
        
        if (!$courier instanceof ShipmentInterface) {
            throw new \RuntimeException(
                "Courier '{$courierName}' does not support shipment creation."
            );
        }
        
        return $courier->createShipment($shipment);
    }
    
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
    
    public function estimateRate(string $courierName, Rate|array $rateRequest): Rate
    {
        $rateRequest = DtoNormalizer::rate($rateRequest);
        
        $courier = $this->resolver->resolve($courierName);
        
        if (!$courier instanceof RateInterface) {
            throw new \RuntimeException(
                "Courier '{$courierName}' does not support rate estimation."
            );
        }
        
        return $courier->estimateRate($rateRequest);
    }
    
    public function getStores(string $courierName, array $filters = []): array
    {
        $courier = $this->resolver->resolve($courierName);
        
        if (!$courier instanceof StoreInterface) {
            throw new \RuntimeException(
                "Courier '{$courierName}' does not support store operations."
            );
        }
        
        return $courier->getStores($filters);
    }
    
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
    
    public function getAvailableCouriers(): array
    {
        return $this->registry->getRegisteredNames();
    }
    
    public function findCouriersByCapabilities(array $capabilities): array
    {
        return $this->resolver->findByCapabilities($capabilities);
    }
}
