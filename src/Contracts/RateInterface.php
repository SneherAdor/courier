<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Rate;

interface RateInterface
{
    public function estimateRate(Rate|array $rateRequest): Rate;

    public function getServiceTypes(): array;
}
