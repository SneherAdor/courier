<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\Contracts\CourierInterface;

/**
 * Registry for managing courier driver instances.
 * 
 * This is the central registry where all courier drivers are registered.
 */
class CourierRegistry
{
    /**
     * @var array<string, CourierInterface>
     */
    private array $couriers = [];
    
    /**
     * @var array<string, callable>
     */
    private array $factories = [];
    
    /**
     * Register a courier driver.
     * 
     * @param CourierInterface $courier
     * @return void
     */
    public function register(CourierInterface $courier): void
    {
        $this->couriers[$courier->getName()] = $courier;
    }
    
    /**
     * Register a courier factory (lazy loading).
     * 
     * @param string $name
     * @param callable $factory Function that returns CourierInterface
     * @return void
     */
    public function registerFactory(string $name, callable $factory): void
    {
        $this->factories[$name] = $factory;
    }
    
    /**
     * Get a courier by name.
     * 
     * @param string $name
     * @return CourierInterface
     * @throws \RuntimeException If courier not found
     */
    public function get(string $name): CourierInterface
    {
        // Check if already instantiated
        if (isset($this->couriers[$name])) {
            return $this->couriers[$name];
        }
        
        // Try to instantiate from factory
        if (isset($this->factories[$name])) {
            $courier = call_user_func($this->factories[$name]);
            $this->couriers[$name] = $courier;
            return $courier;
        }
        
        throw new \RuntimeException("Courier '{$name}' is not registered.");
    }
    
    /**
     * Check if a courier is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->couriers[$name]) || isset($this->factories[$name]);
    }
    
    /**
     * Get all registered courier names.
     * 
     * @return array<string>
     */
    public function getRegisteredNames(): array
    {
        $names = array_keys($this->couriers);
        $names = array_merge($names, array_keys($this->factories));
        return array_unique($names);
    }
    
    /**
     * Get all instantiated couriers.
     * 
     * @return array<string, CourierInterface>
     */
    public function all(): array
    {
        // Instantiate all factories
        foreach ($this->factories as $name => $factory) {
            if (!isset($this->couriers[$name])) {
                $this->couriers[$name] = call_user_func($factory);
            }
        }
        
        return $this->couriers;
    }
    
    /**
     * Remove a courier from registry.
     */
    public function unregister(string $name): void
    {
        unset($this->couriers[$name]);
        unset($this->factories[$name]);
    }
    
    /**
     * Clear all registered couriers.
     */
    public function clear(): void
    {
        $this->couriers = [];
        $this->factories = [];
    }
}
