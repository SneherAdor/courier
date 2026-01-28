<?php

namespace Millat\DeshCourier\Contracts;

interface StoreInterface
{
    /**
     * Get merchant store information
     * 
     * @param array $filters Optional query parameters (e.g., page, per_page)
     * @return array Store list with pagination metadata
     */
    public function getStores(array $filters = []): array;

    /**
     * Get a single store by ID
     * 
     * @param int $storeId The store ID
     * @return array|null Store data or null if not found
     */
    public function getStore(int $storeId): ?array;

    /**
     * Get default store
     * 
     * @return array|null Default store data or null if not found
     */
    public function getDefaultStore(): ?array;
}
