<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Rate;

/**
 * Core courier interface that all courier drivers must implement.
 * 
 * This interface defines the minimum contract for courier operations.
 * Drivers can implement additional interfaces for extended capabilities.
 */
interface CourierInterface
{
    /**
     * Get the unique identifier for this courier driver.
     * 
     * @return string e.g., 'pathao', 'steadfast', 'redx'
     */
    public function getName(): string;

    /**
     * Get the display name for this courier.
     * 
     * @return string e.g., 'Pathao Courier'
     */
    public function getDisplayName(): string;

    /**
     * Declare what capabilities this courier driver supports.
     * 
     * This allows the SDK to gracefully degrade when features aren't available.
     * 
     * @return array<string> List of capability keys:
     *   - 'shipment.create'
     *   - 'shipment.update'
     *   - 'shipment.cancel'
     *   - 'shipment.bulk'
     *   - 'shipment.label'
     *   - 'shipment.pickup'
     *   - 'tracking.realtime'
     *   - 'tracking.webhook'
     *   - 'rate.estimation'
     *   - 'cod.settlement'
     *   - 'cod.tracking'
     *   - 'metadata.cities'
     *   - 'metadata.zones'
     */
    public function capabilities(): array;

    /**
     * Check if this courier supports a specific capability.
     * 
     * @param string $capability
     * @return bool
     */
    public function supports(string $capability): bool;

    /**
     * Test the connection/authentication with the courier API.
     * 
     * @return bool
     */
    public function testConnection(): bool;
}
