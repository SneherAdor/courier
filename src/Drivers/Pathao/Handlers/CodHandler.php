<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Exceptions\ApiException;

class CodHandler extends PathaoHandler
{
    public function getDetails(string $trackingId): Cod
    {
        $response = $this->get("aladdin/api/v1/orders/{$trackingId}/cod", [
            'headers' => $this->getAuthHeaders(['Content-Type' => null]),
        ]);
        
        $this->handleResponse($response, [200], 'Failed to get COD details');
        
        return $this->getMapper()->mapApiToCod($response['data']);
    }
    
    public function getLedger(array $filters = []): array
    {
        $response = $this->get('aladdin/api/v1/cod/ledger', [
            'headers' => $this->getAuthHeaders(['Content-Type' => null]),
            'query' => $filters,
        ]);
        
        $this->handleResponse($response, [200], 'Failed to get COD ledger');
        
        return $this->mapCodRecords($response['data']['cod_records'] ?? []);
    }
    
    public function reconcile(array $trackingIds, array $settlementData = []): bool
    {
        $response = $this->post('aladdin/api/v1/cod/reconcile', [
            'json' => array_merge(
                ['tracking_ids' => $trackingIds],
                $settlementData
            ),
        ]);
        
        return in_array($response['status'], [200, 201]);
    }
    
    protected function mapCodRecords(array $codRecords): array
    {
        return array_map(
            fn(array $codData) => $this->getMapper()->mapApiToCod($codData),
            $codRecords
        );
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
