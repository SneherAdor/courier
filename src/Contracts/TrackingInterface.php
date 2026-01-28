<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Tracking;

interface TrackingInterface
{
    public function track(string $trackingId): Tracking;

    public function trackBulk(array $trackingIds): array;

    public function getStatus(string $trackingId): string;
}
