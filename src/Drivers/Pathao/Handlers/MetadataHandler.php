<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\Exceptions\ApiException;

class MetadataHandler extends PathaoHandler
{
    public function getCities(): array
    {
        $response = $this->get('aladdin/api/v1/cities', [
            'headers' => $this->getAuthHeaders(['Content-Type' => null]),
        ]);
        
        $this->handleResponse($response, [200], 'Failed to get cities');
        
        return $response['data']['cities'] ?? [];
    }
    
    public function getZones(?int $cityId = null): array
    {
        $query = $cityId ? ['city_id' => $cityId] : [];
        
        $response = $this->get('aladdin/api/v1/zones', [
            'headers' => $this->getAuthHeaders(['Content-Type' => null]),
            'query' => $query,
        ]);
        
        $this->handleResponse($response, [200], 'Failed to get zones');
        
        return $response['data']['zones'] ?? [];
    }
    
    public function getAreas(?int $zoneId = null): array
    {
        $query = $zoneId ? ['zone_id' => $zoneId] : [];
        
        $response = $this->get('aladdin/api/v1/areas', [
            'headers' => $this->getAuthHeaders(['Content-Type' => null]),
            'query' => $query,
        ]);
        
        $this->handleResponse($response, [200], 'Failed to get areas');
        
        return $response['data']['areas'] ?? [];
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
