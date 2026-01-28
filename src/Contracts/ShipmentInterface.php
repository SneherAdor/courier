<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Shipment;

interface ShipmentInterface
{
    public function createShipment(Shipment|array $shipment): Shipment;

    public function updateShipment(string $trackingId, Shipment|array $shipment): Shipment;

    public function cancelShipment(string $trackingId, ?string $reason = null): bool;

    public function createBulkShipments(array $shipments): array;

    public function generateLabel(string $trackingId, string $format = 'pdf'): string;

    public function requestPickup(string $trackingId, array $pickupDetails = []): bool;
}
