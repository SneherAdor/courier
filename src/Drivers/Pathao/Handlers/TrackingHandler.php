<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\Core\StatusMapper;
use Millat\DeshCourier\Exceptions\ApiException;

class TrackingHandler extends PathaoHandler
{
    public function track(string $trackingId): Tracking
    {
        $response = $this->get("aladdin/api/v1/orders/{$trackingId}/track", [
            'headers' => $this->getAuthHeaders(['Content-Type' => null]),
        ]);
        
        $this->handleResponse($response, [200], 'Failed to track shipment');
        
        return $this->getMapper()->mapApiToTracking($response['data']);
    }
    
    public function trackBulk(array $trackingIds): array
    {
        $response = $this->post('aladdin/api/v1/orders/track/bulk', [
            'json' => ['tracking_ids' => $trackingIds],
        ]);
        
        $this->handleResponse($response, [200], 'Failed to track shipments');
        
        return $this->mapBulkTrackings($response['data']['trackings'] ?? []);
    }
    
    public function getStatus(string $trackingId): string
    {
        $tracking = $this->track($trackingId);
        
        return $tracking->status ?? StatusMapper::CREATED;
    }
    
    protected function mapBulkTrackings(array $trackingData): array
    {
        $results = [];
        
        foreach ($trackingData as $data) {
            $trackingId = $data['tracking_id'] ?? '';
            $results[$trackingId] = $this->getMapper()->mapApiToTracking($data);
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
