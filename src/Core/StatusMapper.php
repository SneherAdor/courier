<?php

namespace Millat\DeshCourier\Core;

/**
 * Maps courier-specific statuses to normalized canonical statuses.
 * 
 * This ensures all couriers use the same status vocabulary.
 */
class StatusMapper
{
    /**
     * Canonical status constants.
     */
    public const CREATED = 'CREATED';
    public const PICKED = 'PICKED';
    public const IN_TRANSIT = 'IN_TRANSIT';
    public const OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY';
    public const DELIVERED = 'DELIVERED';
    public const FAILED = 'FAILED';
    public const RETURNED = 'RETURNED';
    public const CANCELLED = 'CANCELLED';
    
    /**
     * All canonical statuses.
     */
    public static function getCanonicalStatuses(): array
    {
        return [
            self::CREATED,
            self::PICKED,
            self::IN_TRANSIT,
            self::OUT_FOR_DELIVERY,
            self::DELIVERED,
            self::FAILED,
            self::RETURNED,
            self::CANCELLED,
        ];
    }
    
    /**
     * Map a courier-specific status to canonical status.
     * 
     * Drivers should provide their own mapping logic, but this provides
     * a default implementation that can be extended.
     * 
     * @param string $courierStatus Raw status from courier
     * @param array<string, string> $customMapping Custom mapping array
     * @return string Canonical status
     */
    public static function map(string $courierStatus, array $customMapping = []): string
    {
        // Use custom mapping if provided
        if (isset($customMapping[$courierStatus])) {
            return $customMapping[$courierStatus];
        }
        
        // Default case-insensitive mapping
        $normalized = strtoupper(trim($courierStatus));
        
        // Common patterns
        $patterns = [
            '/CREATED|PENDING|BOOKED|CONFIRMED/i' => self::CREATED,
            '/PICKED|PICKUP|COLLECTED/i' => self::PICKED,
            '/IN.?TRANSIT|TRANSIT|SHIPPED/i' => self::IN_TRANSIT,
            '/OUT.?FOR.?DELIVERY|ON.?DELIVERY|DELIVERING/i' => self::OUT_FOR_DELIVERY,
            '/DELIVERED|COMPLETED|SUCCESS/i' => self::DELIVERED,
            '/FAILED|FAILURE|UNDELIVERED/i' => self::FAILED,
            '/RETURNED|RETURN/i' => self::RETURNED,
            '/CANCELLED|CANCELED/i' => self::CANCELLED,
        ];
        
        foreach ($patterns as $pattern => $canonical) {
            if (preg_match($pattern, $normalized)) {
                return $canonical;
            }
        }
        
        // Default to CREATED if no match
        return self::CREATED;
    }
    
    /**
     * Check if a status is terminal (final state).
     */
    public static function isTerminal(string $status): bool
    {
        return in_array($status, [
            self::DELIVERED,
            self::FAILED,
            self::RETURNED,
            self::CANCELLED,
        ]);
    }
    
    /**
     * Get status display name.
     */
    public static function getDisplayName(string $status): string
    {
        $names = [
            self::CREATED => 'Created',
            self::PICKED => 'Picked Up',
            self::IN_TRANSIT => 'In Transit',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
            self::RETURNED => 'Returned',
            self::CANCELLED => 'Cancelled',
        ];
        
        return $names[$status] ?? $status;
    }
}
