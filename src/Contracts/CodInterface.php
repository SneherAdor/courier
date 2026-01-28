<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Cod;

interface CodInterface
{
    public function getCodDetails(string $trackingId): Cod;

    public function getCodLedger(array $filters = []): array;

    public function reconcileCod(array $trackingIds, array $settlementData = []): bool;
}
