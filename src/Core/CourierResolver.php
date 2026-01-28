<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\Contracts\CourierInterface;

class CourierResolver
{
    private CourierRegistry $registry;
    
    public function __construct(CourierRegistry $registry)
    {
        $this->registry = $registry;
    }
    
    public function resolve(string $name): CourierInterface
    {
        return $this->registry->get($name);
    }
    
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
    
    public function getDefault(): ?CourierInterface
    {
        $names = $this->registry->getRegisteredNames();
        if (empty($names)) {
            return null;
        }
        
        return $this->registry->get($names[0]);
    }
    
    public function getAll(): array
    {
        return $this->registry->all();
    }
}
