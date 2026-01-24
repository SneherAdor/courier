<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Shipment;

/**
 * Interface for courier drivers that support shipment operations.
 */
interface ShipmentInterface
{
    /**
     * Create a new shipment.
     * 
     * @param Shipment|array<string, mixed> $shipment Shipment DTO or array of shipment data
     * @return Shipment Updated DTO with tracking ID and courier-specific data
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function createShipment(Shipment|array $shipment): Shipment;

    /**
     * Update an existing shipment.
     * 
     * @param string $trackingId The courier's tracking ID
     * @param Shipment|array<string, mixed> $shipment Updated shipment data
     * @return Shipment
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function updateShipment(string $trackingId, Shipment|array $shipment): Shipment;

    /**
     * Cancel a shipment.
     * 
     * @param string $trackingId
     * @param string|null $reason Optional cancellation reason
     * @return bool
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function cancelShipment(string $trackingId, ?string $reason = null): bool;

    /**
     * Create multiple shipments in a single request (if supported).
     * 
     * @param array<Shipment|array<string, mixed>> $shipments Array of Shipment DTOs or arrays
     * @return array<Shipment>
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function createBulkShipments(array $shipments): array;

    /**
     * Generate a shipping label for a shipment.
     * 
     * @param string $trackingId
     * @param string $format 'pdf' or 'image'
     * @return string Base64-encoded label or URL
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function generateLabel(string $trackingId, string $format = 'pdf'): string;

    /**
     * Request a pickup for a shipment.
     * 
     * @param string $trackingId
     * @param array<string, mixed> $pickupDetails
     * @return bool
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function requestPickup(string $trackingId, array $pickupDetails = []): bool;
}
