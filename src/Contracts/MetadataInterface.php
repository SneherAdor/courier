<?php

namespace Millat\DeshCourier\Contracts;

/**
 * Interface for courier drivers that provide metadata about supported areas.
 */
interface MetadataInterface
{
    /**
     * Get list of supported cities.
     * 
     * @return array<string, string> City code => City name
     */
    public function getSupportedCities(): array;

    /**
     * Get list of supported zones/areas within a city.
     * 
     * @param string $cityCode
     * @return array<string, string> Zone/Area code => Zone/Area name
     */
    public function getSupportedZones(string $cityCode): array;

    /**
     * Check if a city is supported.
     * 
     * @param string $cityCode
     * @return bool
     */
    public function isCitySupported(string $cityCode): bool;

    /**
     * Get delivery SLAs for different service types.
     * 
     * @return array<string, array> Service type => SLA details
     */
    public function getDeliverySlas(): array;
}
