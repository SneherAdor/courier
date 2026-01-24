<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Rate;

/**
 * Interface for courier drivers that support rate estimation.
 */
interface RateInterface
{
    /**
     * Estimate delivery charges for a shipment.
     * 
     * @param Rate|array<string, mixed> $rateRequest Rate DTO or array of rate data
     * @return Rate Updated DTO with calculated rates
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function estimateRate(Rate|array $rateRequest): Rate;

    /**
     * Get available service types (Same Day, Next Day, Express, etc.).
     * 
     * @return array<string, array> Service types with metadata
     */
    public function getServiceTypes(): array;
}
