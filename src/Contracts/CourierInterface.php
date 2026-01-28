<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Rate;

interface CourierInterface
{
    public function getName(): string;

    public function getDisplayName(): string;

    public function capabilities(): array;

    public function supports(string $capability): bool;

    public function testConnection(): bool;
}
