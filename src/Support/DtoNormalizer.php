<?php

namespace Millat\DeshCourier\Support;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;

/**
 * Normalizes arrays to DTO objects.
 * 
 * This class ensures type safety by converting arrays to proper DTO instances.
 */
class DtoNormalizer
{
    /**
     * Normalize shipment data to Shipment DTO.
     * 
     * @param Shipment|array<string, mixed> $data
     * @return Shipment
     */
    public static function shipment(Shipment|array $data): Shipment
    {
        if ($data instanceof Shipment) {
            return $data;
        }
        
        return new Shipment($data);
    }
    
    /**
     * Normalize tracking data to Tracking DTO.
     * 
     * @param Tracking|array<string, mixed> $data
     * @return Tracking
     */
    public static function tracking(Tracking|array $data): Tracking
    {
        if ($data instanceof Tracking) {
            return $data;
        }
        
        return new Tracking($data);
    }
    
    /**
     * Normalize rate data to Rate DTO.
     * 
     * @param Rate|array<string, mixed> $data
     * @return Rate
     */
    public static function rate(Rate|array $data): Rate
    {
        if ($data instanceof Rate) {
            return $data;
        }
        
        return new Rate($data);
    }
    
    /**
     * Normalize COD data to Cod DTO.
     * 
     * @param Cod|array<string, mixed> $data
     * @return Cod
     */
    public static function cod(Cod|array $data): Cod
    {
        if ($data instanceof Cod) {
            return $data;
        }
        
        return new Cod($data);
    }
    
    /**
     * Normalize array of shipments to array of Shipment DTOs.
     * 
     * @param array<Shipment|array<string, mixed>> $data
     * @return array<Shipment>
     */
    public static function shipments(array $data): array
    {
        return array_map(fn($item) => self::shipment($item), $data);
    }
    
    /**
     * Normalize array of tracking data to array of Tracking DTOs.
     * 
     * @param array<Tracking|array<string, mixed>> $data
     * @return array<Tracking>
     */
    public static function trackings(array $data): array
    {
        return array_map(fn($item) => self::tracking($item), $data);
    }
}
