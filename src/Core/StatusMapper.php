<?php

namespace Millat\DeshCourier\Core;

class StatusMapper
{
    public const CREATED = 'CREATED';
    public const PICKED = 'PICKED';
    public const IN_TRANSIT = 'IN_TRANSIT';
    public const OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY';
    public const DELIVERED = 'DELIVERED';
    public const FAILED = 'FAILED';
    public const RETURNED = 'RETURNED';
    public const CANCELLED = 'CANCELLED';
    
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
    
    public static function map(string $courierStatus, array $customMapping = []): string
    {
        if (isset($customMapping[$courierStatus])) {
            return $customMapping[$courierStatus];
        }
        
        $normalized = strtoupper(trim($courierStatus));
        
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
        
        return self::CREATED;
    }
    
    public static function isTerminal(string $status): bool
    {
        return in_array($status, [
            self::DELIVERED,
            self::FAILED,
            self::RETURNED,
            self::CANCELLED,
        ]);
    }
    
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
