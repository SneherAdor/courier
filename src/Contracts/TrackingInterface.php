<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Tracking;

/**
 * Interface for courier drivers that support tracking operations.
 */
interface TrackingInterface
{
    /**
     * Track a shipment by tracking ID.
     * 
     * @param string $trackingId
     * @return Tracking
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function track(string $trackingId): Tracking;

    /**
     * Track multiple shipments at once.
     * 
     * @param array<string> $trackingIds
     * @return array<string, Tracking> Keyed by tracking ID
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function trackBulk(array $trackingIds): array;

    /**
     * Get the current status of a shipment (lightweight check).
     * 
     * @param string $trackingId
     * @return string Normalized status (CREATED, PICKED, IN_TRANSIT, etc.)
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function getStatus(string $trackingId): string;
}
