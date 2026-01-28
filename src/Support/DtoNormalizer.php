<?php

namespace Millat\DeshCourier\Support;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;

class DtoNormalizer
{
    public static function shipment(Shipment|array $data): Shipment
    {
        if ($data instanceof Shipment) {
            return $data;
        }
        
        return new Shipment($data);
    }
    
    public static function tracking(Tracking|array $data): Tracking
    {
        if ($data instanceof Tracking) {
            return $data;
        }
        
        return new Tracking($data);
    }
    
    public static function rate(Rate|array $data): Rate
    {
        if ($data instanceof Rate) {
            return $data;
        }
        
        return new Rate($data);
    }
    
    public static function cod(Cod|array $data): Cod
    {
        if ($data instanceof Cod) {
            return $data;
        }
        
        return new Cod($data);
    }
    
    public static function shipments(array $data): array
    {
        return array_map(fn($item) => self::shipment($item), $data);
    }
    
    public static function trackings(array $data): array
    {
        return array_map(fn($item) => self::tracking($item), $data);
    }
}
