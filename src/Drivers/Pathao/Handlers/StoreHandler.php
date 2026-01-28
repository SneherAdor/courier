<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\Exceptions\ApiException;

class StoreHandler extends PathaoHandler
{
    public function getStores(array $filters = []): array
    {
        $response = $this->get('aladdin/api/v1/stores', [
            'headers' => $this->getAuthHeaders(['Content-Type' => 'application/json; charset=UTF-8']),
            'query' => $filters,
        ]);
        
        dd($response);
        $this->handleResponse($response, [200], 'Failed to get store list');
        
        return $this->mapStoreResponse($response['data']);
    }
    
    public function getStore(int $storeId): ?array
    {
        $stores = $this->getStores();
        
        foreach ($stores['data'] ?? [] as $store) {
            if (isset($store['store_id']) && (int)$store['store_id'] === $storeId) {
                return $store;
            }
        }
        
        return null;
    }
    
    public function getDefaultStore(): ?array
    {
        $stores = $this->getStores();
        
        foreach ($stores['data'] ?? [] as $store) {
            if (isset($store['is_default_store']) && $store['is_default_store'] === true) {
                return $store;
            }
        }
        
        return null;
    }
    
    protected function mapStoreResponse(array $apiData): array
    {
        $stores = [];
        
        // According to API docs, structure is: response['data']['data'] contains stores array
        // $apiData is response['data'], so we need $apiData['data'] for stores
        $storesData = $apiData['data'] ?? null;
        
        // Handle different possible structures
        if ($storesData === null) {
            // No stores data
        } elseif (is_array($storesData) && isset($storesData[0]) && is_array($storesData[0])) {
            // Numeric array - stores are directly in $apiData['data']
            foreach ($storesData as $storeData) {
                if (is_array($storeData) && isset($storeData['store_id'])) {
                    $stores[] = $this->getMapper()->mapApiToStore($storeData);
                }
            }
        } elseif (is_array($storesData)) {
            // Associative array - might be nested or mixed
            foreach ($storesData as $key => $value) {
                // Only process values that are arrays with store_id (actual store objects)
                if (is_array($value) && isset($value['store_id'])) {
                    $stores[] = $this->getMapper()->mapApiToStore($value);
                }
            }
        }
        
        return [
            'data' => $stores,
            'total' => $apiData['total'] ?? count($stores),
            'current_page' => $apiData['current_page'] ?? 1,
            'per_page' => $apiData['per_page'] ?? 1000,
            'total_in_page' => $apiData['total_in_page'] ?? count($stores),
            'last_page' => $apiData['last_page'] ?? 1,
            'path' => $apiData['path'] ?? null,
            'to' => $apiData['to'] ?? null,
            'from' => $apiData['from'] ?? null,
            'last_page_url' => $apiData['last_page_url'] ?? null,
            'first_page_url' => $apiData['first_page_url'] ?? null,
        ];
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
