<?php

namespace Millat\DeshCourier\Drivers\Pathao;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Core\StatusMapper;

/**
 * Maps between Pathao API format and SDK DTOs.
 * 
 * This class encapsulates all courier-specific data transformation logic.
 */
class PathaoMapper
{
    /**
     * Map Shipment to Pathao API format.
     */
    public function mapShipmentToApi(Shipment $shipment): array
    {
        // Pathao API requires store_id, item_type as integer, delivery_type as integer
        $payload = [
            'store_id' => (int) ($shipment->courierData['store_id'] ?? null),
            'merchant_order_id' => $shipment->externalOrderId,
            'recipient_name' => $shipment->recipientName,
            'recipient_phone' => $shipment->recipientPhone,
            'recipient_address' => $shipment->recipientAddress,
            'delivery_type' => (int) $this->mapServiceType($shipment->serviceType), // 48 for Normal, 12 for On Demand
            'item_type' => 2, // 1 for Document, 2 for Parcel
            'item_quantity' => (int) ($shipment->quantity ?? 1),
            'item_weight' => (string) $shipment->weight, // Must be string
            'amount_to_collect' => (int) ($shipment->codAmount ?? 0),
        ];
        
        // Optional fields
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
    
    /**
     * Map Pathao API response to Shipment.
     */
    public function mapApiToShipment(array $apiData, ?Shipment $existing = null): Shipment
    {
        $shipment = $existing ?? new Shipment();
        
        // Pathao API wraps response in 'data' field
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
    
    /**
     * Map Pathao API response to Tracking.
     */
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
        
        // Map timestamps
        if (isset($apiData['picked_at'])) {
            $tracking->pickedAt = new \DateTimeImmutable($apiData['picked_at']);
        }
        if (isset($apiData['delivered_at'])) {
            $tracking->deliveredAt = new \DateTimeImmutable($apiData['delivered_at']);
        }
        if (isset($apiData['last_updated_at'])) {
            $tracking->lastUpdatedAt = new \DateTimeImmutable($apiData['last_updated_at']);
        }
        
        // Map history
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
    
    /**
     * Map Rate to Pathao API format.
     */
    public function mapRateToApi(Rate $rate): array
    {
        return [
            'store_id' => $rate->courierData['store_id'] ?? null,
            'item_type' => 'Parcel',
            'delivery_type' => $this->mapServiceType($rate->serviceType),
            'item_weight' => $rate->weight,
            'recipient_city' => $rate->toCity,
            'recipient_zone' => $rate->toZone,
            'amount_collection' => $rate->codAmount,
        ];
    }
    
    /**
     * Map Pathao API response to Rate.
     */
    public function mapApiToRate(array $apiData, Rate $existing): Rate
    {
        $rate = $existing;
        
        $rate->deliveryCharge = $apiData['delivery_charge'] ?? null;
        $rate->codCharge = $apiData['cod_charge'] ?? null;
        $rate->totalCharge = ($rate->deliveryCharge ?? 0) + ($rate->codCharge ?? 0);
        $rate->estimatedDays = $apiData['estimated_days'] ?? null;
        $rate->courierName = 'pathao';
        $rate->breakdown = [
            'delivery_charge' => $rate->deliveryCharge,
            'cod_charge' => $rate->codCharge,
            'total' => $rate->totalCharge,
        ];
        
        if (isset($apiData['estimated_delivery_date'])) {
            $rate->estimatedDeliveryDate = new \DateTimeImmutable($apiData['estimated_delivery_date']);
        }
        
        return $rate;
    }
    
    /**
     * Map Pathao API response to Cod.
     */
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
    
    /**
     * Map service type to Pathao format.
     * Pathao uses: 48 for Normal Delivery, 12 for On Demand Delivery
     */
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
    
    /**
     * Get Pathao-specific status mapping.
     */
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
