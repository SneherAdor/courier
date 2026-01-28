<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\Contracts\CourierInterface;

class CapabilityDetector
{
    public const CAPABILITIES = [
        'shipment.create',
        'shipment.update',
        'shipment.cancel',
        'shipment.bulk',
        'shipment.label',
        'shipment.pickup',
        'tracking.realtime',
        'tracking.webhook',
        'tracking.bulk',
        'rate.estimation',
        'rate.service_types',
        'cod.tracking',
        'cod.settlement',
        'cod.ledger',
        'metadata.cities',
        'metadata.zones',
        'metadata.slas',
    ];
    
    public static function supports(CourierInterface $courier, string $capability): bool
    {
        return $courier->supports($capability);
    }
    
    public static function getSupported(CourierInterface $courier): array
    {
        return $courier->capabilities();
    }
    
    public static function getMissing(CourierInterface $courier): array
    {
        $supported = $courier->capabilities();
        return array_diff(self::CAPABILITIES, $supported);
    }
    
    public static function supportsAll(CourierInterface $courier, array $required): bool
    {
        $supported = $courier->capabilities();
        return empty(array_diff($required, $supported));
    }
    
    public static function supportsAny(CourierInterface $courier, array $required): bool
    {
        $supported = $courier->capabilities();
        return !empty(array_intersect($required, $supported));
    }
}
