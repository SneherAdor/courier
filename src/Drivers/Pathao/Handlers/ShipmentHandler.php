<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\Support\DtoNormalizer;
use Millat\DeshCourier\Exceptions\ApiException;

class ShipmentHandler extends PathaoHandler
{
    public function create(Shipment|array $shipment): Shipment
    {
        $shipment = DtoNormalizer::shipment($shipment);
        
        $this->validateShipment($shipment);
        
        $response = $this->post('aladdin/api/v1/orders', [
            'json' => $this->getMapper()->mapShipmentToApi($shipment),
        ]);
        
        $this->handleResponse($response, [200, 201], 'Failed to create shipment');
        
        return $this->getMapper()->mapApiToShipment($response['data'], $shipment);
    }
    
    public function update(string $trackingId, Shipment|array $shipment): Shipment
    {
        $shipment = DtoNormalizer::shipment($shipment);
        
        $response = $this->put("aladdin/api/v1/orders/{$trackingId}", [
            'json' => $this->getMapper()->mapShipmentToApi($shipment),
        ]);
        
        $this->handleResponse($response, [200], 'Failed to update shipment');
        
        return $this->getMapper()->mapApiToShipment($response['data'], $shipment);
    }
    
    public function cancel(string $trackingId, ?string $reason = null): bool
    {
        $response = $this->delete("aladdin/api/v1/orders/{$trackingId}", [
            'json' => ['reason' => $reason],
        ]);
        
        return in_array($response['status'], [200, 204]);
    }
    
    public function createBulk(array $shipments): array
    {
        $shipments = DtoNormalizer::shipments($shipments);
        
        $payload = array_map(fn(Shipment $shipment) => $this->getMapper()->mapShipmentToApi($shipment), $shipments);
        
        $response = $this->post('aladdin/api/v1/orders/bulk', [
            'json' => ['orders' => $payload],
        ]);
        
        $this->handleResponse($response, [200, 201], 'Failed to create bulk shipments');
        
        return $this->mapBulkShipments($response['data']['orders'] ?? [], $shipments);
    }
    
    public function generateLabel(string $trackingId, string $format = 'pdf'): string
    {
        $response = $this->get("aladdin/api/v1/orders/{$trackingId}/label", [
            'headers' => $this->getAuthHeaders(['Content-Type' => null]),
            'query' => ['format' => $format],
        ]);
        
        $this->handleResponse($response, [200], 'Failed to generate label');
        
        return $response['data']['label_url'] ?? $response['data']['label_base64'] ?? '';
    }
    
    public function requestPickup(string $trackingId, array $pickupDetails = []): bool
    {
        $response = $this->post("aladdin/api/v1/orders/{$trackingId}/pickup", [
            'json' => $pickupDetails,
        ]);
        
        return in_array($response['status'], [200, 201]);
    }
    
    protected function validateShipment(Shipment $shipment): void
    {
        $errors = $shipment->validateForCreation();
        
        if (!empty($errors)) {
            throw new ApiException(
                "Shipment validation failed: " . implode(', ', $errors),
                0,
                null,
                'pathao'
            );
        }
    }
    
    protected function mapBulkShipments(array $orderData, array $shipments): array
    {
        $results = [];
        
        foreach ($orderData as $index => $data) {
            $results[] = $this->getMapper()->mapApiToShipment(
                $data,
                $shipments[$index] ?? new Shipment()
            );
        }
        
        return $results;
    }
    
    protected function handleResponse(array $response, array $successStatuses, string $defaultMessage): void
    {
        if (!in_array($response['status'], $successStatuses)) {
            throw new ApiException(
                $response['data']['message'] ?? $defaultMessage,
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data'] ?? []
            );
        }
    }
}
