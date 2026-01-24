<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Cod;

/**
 * Interface for courier drivers that support COD operations.
 */
interface CodInterface
{
    /**
     * Get COD details for a shipment.
     * 
     * @param string $trackingId
     * @return Cod
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function getCodDetails(string $trackingId): Cod;

    /**
     * Get COD ledger/settlement report.
     * 
     * @param array<string, mixed> $filters Date range, status, etc.
     * @return array<Cod>
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function getCodLedger(array $filters = []): array;

    /**
     * Reconcile COD amounts (mark as settled).
     * 
     * @param array<string> $trackingIds
     * @param array<string, mixed> $settlementData
     * @return bool
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function reconcileCod(array $trackingIds, array $settlementData = []): bool;
}
