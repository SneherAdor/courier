<?php

namespace Millat\DeshCourier\Contracts;

interface MetadataInterface
{
    public function getSupportedCities(): array;

    public function getSupportedZones(string $cityCode): array;

    public function isCitySupported(string $cityCode): bool;

    public function getDeliverySlas(): array;
}
