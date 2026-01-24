<?php

namespace Millat\DeshCourier\Core;

use Millat\DeshCourier\Contracts\CourierInterface;

/**
 * Detects and validates courier capabilities.
 */
class CapabilityDetector
{
    /**
     * All known capabilities.
     */
    public const CAPABILITIES = [
        // Shipment capabilities
        'shipment.create',
        'shipment.update',
        'shipment.cancel',
        'shipment.bulk',
        'shipment.label',
        'shipment.pickup',
        
        // Tracking capabilities
        'tracking.realtime',
        'tracking.webhook',
        'tracking.bulk',
        
        // Rate capabilities
        'rate.estimation',
        'rate.service_types',
        
        // COD capabilities
        'cod.tracking',
        'cod.settlement',
        'cod.ledger',
        
        // Metadata capabilities
        'metadata.cities',
        'metadata.zones',
        'metadata.slas',
    ];
    
    /**
     * Check if a courier supports a capability.
     */
    public static function supports(CourierInterface $courier, string $capability): bool
    {
        return $courier->supports($capability);
    }
    
    /**
     * Get all capabilities supported by a courier.
     */
    public static function getSupported(CourierInterface $courier): array
    {
        return $courier->capabilities();
    }
    
    /**
     * Get missing capabilities (what the courier doesn't support).
     */
    public static function getMissing(CourierInterface $courier): array
    {
        $supported = $courier->capabilities();
        return array_diff(self::CAPABILITIES, $supported);
    }
    
    /**
     * Check if courier supports all required capabilities.
     */
    public static function supportsAll(CourierInterface $courier, array $required): bool
    {
        $supported = $courier->capabilities();
        return empty(array_diff($required, $supported));
    }
    
    /**
     * Check if courier supports any of the required capabilities.
     */
    public static function supportsAny(CourierInterface $courier, array $required): bool
    {
        $supported = $courier->capabilities();
        return !empty(array_intersect($required, $supported));
    }
}
