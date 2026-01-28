<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\Contracts\CourierInterface;

class CourierRegistry
{
    private array $couriers = [];
    private array $factories = [];
    
    public function register(CourierInterface $courier): void
    {
        $this->couriers[$courier->getName()] = $courier;
    }
    
    public function registerFactory(string $name, callable $factory): void
    {
        $this->factories[$name] = $factory;
    }
    
    public function get(string $name): CourierInterface
    {
        if (isset($this->couriers[$name])) {
            return $this->couriers[$name];
        }
        
        if (isset($this->factories[$name])) {
            $courier = call_user_func($this->factories[$name]);
            $this->couriers[$name] = $courier;
            return $courier;
        }
        
        throw new \RuntimeException("Courier '{$name}' is not registered.");
    }
    
    public function has(string $name): bool
    {
        return isset($this->couriers[$name]) || isset($this->factories[$name]);
    }
    
    public function getRegisteredNames(): array
    {
        $names = array_keys($this->couriers);
        $names = array_merge($names, array_keys($this->factories));
        return array_unique($names);
    }
    
    public function all(): array
    {
        foreach ($this->factories as $name => $factory) {
            if (!isset($this->couriers[$name])) {
                $this->couriers[$name] = call_user_func($factory);
            }
        }
        
        return $this->couriers;
    }
    
    public function unregister(string $name): void
    {
        unset($this->couriers[$name]);
        unset($this->factories[$name]);
    }
    
    public function clear(): void
    {
        $this->couriers = [];
        $this->factories = [];
    }
}
