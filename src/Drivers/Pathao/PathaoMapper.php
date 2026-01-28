<?php

namespace Millat\DeshCourier\Drivers\Pathao;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Core\StatusMapper;

class PathaoMapper
{
    public function mapShipmentToApi(Shipment $shipment): array
    {
        $payload = [
            'store_id' => (int) ($shipment->courierData['store_id'] ?? null),
            'merchant_order_id' => $shipment->externalOrderId,
            'recipient_name' => $shipment->recipientName,
            'recipient_phone' => $shipment->recipientPhone,
            'recipient_address' => $shipment->recipientAddress,
            'delivery_type' => (int) $this->mapServiceType($shipment->serviceType),
            'item_type' => 2,
            'item_quantity' => (int) ($shipment->quantity ?? 1),
            'item_weight' => (string) $shipment->weight,
            'amount_to_collect' => (int) ($shipment->codAmount ?? 0),
        ];
        if ($shipment->recipientCity) {
            $payload['recipient_city'] = (int) $shipment->recipientCity;
        }
        if ($shipment->recipientZone) {
            $payload['recipient_zone'] = (int) $shipment->recipientZone;
        }
        if ($shipment->recipientPostalCode) {
            $payload['recipient_area'] = (int) $shipment->recipientPostalCode;
        }
        if ($shipment->deliveryInstruction) {
            $payload['special_instruction'] = $shipment->deliveryInstruction;
        }
        if ($shipment->itemDescription) {
            $payload['item_description'] = $shipment->itemDescription;
        }
        if ($shipment->recipientEmail) {
            $payload['recipient_secondary_phone'] = $shipment->recipientEmail;
        }
        
        return $payload;
    }
    
    public function mapApiToShipment(array $apiData, ?Shipment $existing = null): Shipment
    {
        $shipment = $existing ?? new Shipment();
        
        $data = $apiData['data'] ?? $apiData;
        
        $shipment->trackingId = $data['consignment_id'] ?? $data['tracking_id'] ?? $data['order_id'] ?? null;
        $shipment->courierName = 'pathao';
        $shipment->externalOrderId = $data['merchant_order_id'] ?? null;
        $shipment->status = StatusMapper::map($data['status'] ?? 'CREATED', $this->getStatusMapping());
        $shipment->courierStatus = $data['status'] ?? null;
        $shipment->labelUrl = $data['label_url'] ?? null;
        $shipment->courierData = $data;
        
        if (isset($data['created_at'])) {
            $shipment->createdAt = new \DateTimeImmutable($data['created_at']);
        }
        
        return $shipment;
    }
    
    public function mapApiToTracking(array $apiData): Tracking
    {
        $tracking = new Tracking();
        
        $tracking->trackingId = $apiData['consignment_id'] ?? $apiData['tracking_id'] ?? null;
        $tracking->courierName = 'pathao';
        $tracking->status = StatusMapper::map($apiData['status'] ?? 'CREATED', $this->getStatusMapping());
        $tracking->courierStatus = $apiData['status'] ?? null;
        $tracking->statusDescription = $apiData['status_description'] ?? null;
        $tracking->currentLocation = $apiData['current_location'] ?? null;
        $tracking->deliveredTo = $apiData['delivered_to'] ?? null;
        $tracking->deliveryNote = $apiData['delivery_note'] ?? null;
        $tracking->codAmount = $apiData['cod_amount'] ?? null;
        $tracking->codCollected = $apiData['cod_collected'] ?? null;
        
        if (isset($apiData['picked_at'])) {
            $tracking->pickedAt = new \DateTimeImmutable($apiData['picked_at']);
        }
        if (isset($apiData['delivered_at'])) {
            $tracking->deliveredAt = new \DateTimeImmutable($apiData['delivered_at']);
        }
        if (isset($apiData['last_updated_at'])) {
            $tracking->lastUpdatedAt = new \DateTimeImmutable($apiData['last_updated_at']);
        }
        
        if (isset($apiData['history']) && is_array($apiData['history'])) {
            $tracking->history = array_map(function ($item) {
                return [
                    'status' => StatusMapper::map($item['status'] ?? '', $this->getStatusMapping()),
                    'courier_status' => $item['status'] ?? null,
                    'description' => $item['description'] ?? null,
                    'location' => $item['location'] ?? null,
                    'timestamp' => isset($item['timestamp']) ? new \DateTimeImmutable($item['timestamp']) : null,
                ];
            }, $apiData['history']);
        }
        
        return $tracking;
    }
    
    public function mapRateToApi(Rate $rate): array
    {
        return [
            'store_id' => (int) $rate->storeId,
            'item_type' => 2, // 1 for Document, 2 for Parcel
            'delivery_type' => (int) $rate->deliveryType,
            'item_weight' => (float) $rate->weight,
            'recipient_city' => (int) $rate->toCity,
            'recipient_zone' => (int) $rate->toZone,
        ];
    }
    
    public function mapApiToRate(array $apiData, Rate $existing): Rate
    {
        $rate = $existing;
        
        // Handle nested data structure (response['data']['data'] or response['data'])
        $data = $apiData['data'] ?? $apiData;
        
        // Map from new API response format
        $price = (float) ($data['price'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);
        $promoDiscount = (float) ($data['promo_discount'] ?? 0);
        $additionalCharge = (float) ($data['additional_charge'] ?? 0);
        $finalPrice = (float) ($data['final_price'] ?? $price);
        
        // Calculate COD charge if COD amount is provided
        $codCharge = 0;
        if ($rate->codAmount && $rate->codAmount > 0 && isset($data['cod_percentage'])) {
            $codCharge = $rate->codAmount * (float) $data['cod_percentage'];
        }
        
        $rate->deliveryCharge = $finalPrice;
        $rate->codCharge = $codCharge;
        $rate->totalCharge = $finalPrice + $codCharge;
        $rate->courierName = 'pathao';
        $rate->breakdown = [
            'price' => $price,
            'discount' => $discount,
            'promo_discount' => $promoDiscount,
            'additional_charge' => $additionalCharge,
            'final_price' => $finalPrice,
            'cod_charge' => $codCharge,
            'total' => $rate->totalCharge,
            'plan_id' => $data['plan_id'] ?? null,
            'cod_enabled' => (bool) ($data['cod_enabled'] ?? false),
            'cod_percentage' => isset($data['cod_percentage']) ? (float) $data['cod_percentage'] : null,
        ];
        
        // Store raw API response in courierData for reference
        if (!is_array($rate->courierData)) {
            $rate->courierData = [];
        }
        $rate->courierData['api_response'] = $data;
        
        return $rate;
    }
    
    public function mapApiToCod(array $apiData): Cod
    {
        $cod = new Cod();
        
        $cod->trackingId = $apiData['consignment_id'] ?? $apiData['tracking_id'] ?? null;
        $cod->courierName = 'pathao';
        $cod->codAmount = $apiData['cod_amount'] ?? null;
        $cod->codCollected = $apiData['cod_collected'] ?? null;
        $cod->codPending = ($cod->codAmount ?? 0) - ($cod->codCollected ?? 0);
        $cod->isSettled = $apiData['is_settled'] ?? false;
        $cod->status = $cod->isSettled ? 'settled' : ($cod->codCollected > 0 ? 'collected' : 'pending');
        $cod->settlementReference = $apiData['settlement_reference'] ?? null;
        
        if (isset($apiData['settled_at'])) {
            $cod->settledAt = new \DateTimeImmutable($apiData['settled_at']);
        }
        if (isset($apiData['collected_at'])) {
            $cod->collectedAt = new \DateTimeImmutable($apiData['collected_at']);
        }
        
        return $cod;
    }
    
    public function mapApiToStore(array $apiData): array
    {
        return [
            'store_id' => isset($apiData['store_id']) ? (int) $apiData['store_id'] : null,
            'store_name' => $apiData['store_name'] ?? null,
            'store_address' => $apiData['store_address'] ?? null,
            'is_active' => isset($apiData['is_active']) ? ((int) $apiData['is_active'] === 1) : false,
            'city_id' => isset($apiData['city_id']) ? (int) $apiData['city_id'] : null,
            'zone_id' => isset($apiData['zone_id']) ? (int) $apiData['zone_id'] : null,
            'hub_id' => isset($apiData['hub_id']) ? (int) $apiData['hub_id'] : null,
            'is_default_store' => isset($apiData['is_default_store']) 
                ? ((int) $apiData['is_default_store'] === 1) 
                : false,
            'is_default_return_store' => isset($apiData['is_default_return_store']) 
                ? ((int) $apiData['is_default_return_store'] === 1) 
                : false,
        ];
    }
    
    private function mapServiceType(?string $serviceType): int
    {
        $mapping = [
            'same_day' => 12,      // On Demand Delivery
            'next_day' => 48,      // Normal Delivery
            'express' => 12,        // On Demand Delivery
            'standard' => 48,      // Normal Delivery
        ];
        
        return $mapping[$serviceType ?? 'standard'] ?? 48;
    }
    
    private function getStatusMapping(): array
    {
        return [
            'Pending' => StatusMapper::CREATED,
            'Confirmed' => StatusMapper::CREATED,
            'Picked' => StatusMapper::PICKED,
            'In Transit' => StatusMapper::IN_TRANSIT,
            'Out for Delivery' => StatusMapper::OUT_FOR_DELIVERY,
            'Delivered' => StatusMapper::DELIVERED,
            'Returned' => StatusMapper::RETURNED,
            'Cancelled' => StatusMapper::CANCELLED,
        ];
    }
}
