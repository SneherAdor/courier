<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\Contracts\CourierInterface;

/**
 * Resolves the best courier for a given operation.
 * 
 * This can be extended to implement intelligent courier selection
 * based on capabilities, pricing, SLA, etc.
 */
class CourierResolver
{
    private CourierRegistry $registry;
    
    public function __construct(CourierRegistry $registry)
    {
        $this->registry = $registry;
    }
    
    /**
     * Resolve courier by name.
     */
    public function resolve(string $name): CourierInterface
    {
        return $this->registry->get($name);
    }
    
    /**
     * Find couriers that support specific capabilities.
     * 
     * @param array<string> $requiredCapabilities
     * @return array<string, CourierInterface>
     */
    public function findByCapabilities(array $requiredCapabilities): array
    {
        $matches = [];
        
        foreach ($this->registry->all() as $name => $courier) {
            if (CapabilityDetector::supportsAll($courier, $requiredCapabilities)) {
                $matches[$name] = $courier;
            }
        }
        
        return $matches;
    }
    
    /**
     * Get the default courier (first registered).
     * 
     * @return CourierInterface|null
     */
    public function getDefault(): ?CourierInterface
    {
        $names = $this->registry->getRegisteredNames();
        if (empty($names)) {
            return null;
        }
        
        return $this->registry->get($names[0]);
    }
    
    /**
     * Get all available couriers.
     * 
     * @return array<string, CourierInterface>
     */
    public function getAll(): array
    {
        return $this->registry->all();
    }
}
