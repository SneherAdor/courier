<?php

namespace Millat\DeshCourier\Drivers\Pathao;

use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\Contracts\TrackingInterface;
use Millat\DeshCourier\Contracts\RateInterface;
use Millat\DeshCourier\Contracts\CodInterface;
use Millat\DeshCourier\Contracts\WebhookInterface;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Core\StatusMapper;
use Millat\DeshCourier\Support\HttpClient;
use Millat\DeshCourier\Support\DtoNormalizer;
use Millat\DeshCourier\Exceptions\ApiException;
use Millat\DeshCourier\Exceptions\InvalidConfigurationException;
use Millat\DeshCourier\Support\Validate;

/**
 * Pathao Courier Driver Implementation.
 * 
 * This is an example implementation showing how to create a courier driver.
 * Replace API endpoints and data mapping with actual Pathao API details.
 */
class PathaoCourier implements
    CourierInterface,
    ShipmentInterface,
    TrackingInterface,
    RateInterface,
    CodInterface,
    WebhookInterface
{
    use Validate;

    private PathaoConfig $config;
    private HttpClient $httpClient;
    private PathaoMapper $mapper;
    private ?string $accessToken = null;
    
    public function __construct(PathaoConfig $config, ?HttpClient $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? new HttpClient();
        $this->mapper = new PathaoMapper();
        
        // Validate configuration
        if (empty($this->config->getClientId()) || empty($this->config->getClientSecret())) {
            throw new InvalidConfigurationException(
                "Pathao configuration is incomplete. Client ID and Secret are required.",
                0,
                null,
                'pathao'
            );
        }
    }
    
    // ==================== CourierInterface ====================
    
    public function getName(): string
    {
        return 'pathao';
    }
    
    public function getDisplayName(): string
    {
        return 'Pathao Courier';
    }
    
    public function capabilities(): array
    {
        return [
            'shipment.create',
            'shipment.update',
            'shipment.cancel',
            'shipment.bulk',
            'shipment.label',
            'shipment.pickup',
            'tracking.realtime',
            'tracking.webhook',
            'rate.estimation',
            'cod.tracking',
            'cod.settlement',
            'metadata.cities',
            'metadata.zones',
        ];
    }
    
    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities());
    }
    
    public function testConnection(): bool
    {
        try {
            $this->authenticate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    // ==================== ShipmentInterface ====================
    
    public function createShipment(Shipment|array $shipment): Shipment
    {
        // Normalize to DTO
        $shipment = DtoNormalizer::shipment($shipment);
        
        // Validate
        $errors = $shipment->validateForCreation();
        if (!empty($errors)) {
            throw new ApiException(
                "Shipment validation failed: " . implode(', ', $errors),
                0,
                null,
                'pathao'
            );
        }
        
        // Authenticate if needed
        $this->authenticate();
        
        // Map DTO to Pathao API format
        $payload = $this->mapper->mapShipmentToApi($shipment);
        
        // Make API request
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]
        );
        
        if ($response['status'] !== 200 && $response['status'] !== 201) {
            throw new ApiException(
                $response['data']['message'] ?? 'Failed to create shipment',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        // Map response back to DTO
        return $this->mapper->mapApiToShipment($response['data'], $shipment);
    }
    
    public function updateShipment(string $trackingId, Shipment|array $shipment): Shipment
    {
        // Normalize to DTO
        $shipment = DtoNormalizer::shipment($shipment);
        
        $this->authenticate();
        
        $payload = $this->mapper->mapShipmentToApi($shipment);
        
        $response = $this->httpClient->put(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/' . $trackingId,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                $response['data']['message'] ?? 'Failed to update shipment',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        return $this->mapper->mapApiToShipment($response['data'], $shipment);
    }
    
    public function cancelShipment(string $trackingId, ?string $reason = null): bool
    {
        $this->authenticate();
        
        $response = $this->httpClient->delete(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/' . $trackingId,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
                'json' => ['reason' => $reason],
            ]
        );
        
        return $response['status'] === 200 || $response['status'] === 204;
    }
    
    public function createBulkShipments(array $shipments): array
    {
        // Normalize all shipments to DTOs
        $shipments = DtoNormalizer::shipments($shipments);
        
        $this->authenticate();
        
        $payload = array_map(function (Shipment $shipment) {
            return $this->mapper->mapShipmentToApi($shipment);
        }, $shipments);
        
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/bulk',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['orders' => $payload],
            ]
        );
        
        if ($response['status'] !== 200 && $response['status'] !== 201) {
            throw new ApiException(
                $response['data']['message'] ?? 'Failed to create bulk shipments',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        $results = [];
        foreach ($response['data']['orders'] ?? [] as $index => $orderData) {
            $results[] = $this->mapper->mapApiToShipment($orderData, $shipments[$index] ?? new Shipment());
        }
        
        return $results;
    }
    
    public function generateLabel(string $trackingId, string $format = 'pdf'): string
    {
        $this->authenticate();
        
        $response = $this->httpClient->get(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/' . $trackingId . '/label',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
                'query' => ['format' => $format],
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                'Failed to generate label',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        // Return base64 encoded label or URL
        return $response['data']['label_url'] ?? $response['data']['label_base64'] ?? '';
    }
    
    public function requestPickup(string $trackingId, array $pickupDetails = []): bool
    {
        $this->authenticate();
        
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/' . $trackingId . '/pickup',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $pickupDetails,
            ]
        );
        
        return $response['status'] === 200 || $response['status'] === 201;
    }
    
    // ==================== TrackingInterface ====================
    
    public function track(string $trackingId): Tracking
    {
        $this->authenticate();
        
        $response = $this->httpClient->get(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/' . $trackingId . '/track',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                'Failed to track shipment',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        return $this->mapper->mapApiToTracking($response['data']);
    }
    
    public function trackBulk(array $trackingIds): array
    {
        $this->authenticate();
        
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/track/bulk',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['tracking_ids' => $trackingIds],
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                'Failed to track shipments',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        $results = [];
        foreach ($response['data']['trackings'] ?? [] as $trackingData) {
            $trackingId = $trackingData['tracking_id'] ?? '';
            $results[$trackingId] = $this->mapper->mapApiToTracking($trackingData);
        }
        
        return $results;
    }
    
    public function getStatus(string $trackingId): string
    {
        $tracking = $this->track($trackingId);
        return $tracking->status ?? StatusMapper::CREATED;
    }
    
    // ==================== RateInterface ====================
    
    public function estimateRate(Rate|array $rateRequest): Rate
    {
        $rateRequest = DtoNormalizer::rate($rateRequest);

        $this->validate(
            $rateRequest,
            [
                'weight' => 'required|numeric|min:0.1|max:50',
                'millat' => 'required|numeric|min:0',
            ],
            [
                // Optional: rule-specific messages
                'weight.required' => 'Weight is required.',
                'weight.numeric'  => 'Weight must be a number.',
            ],
            [
                // This is what the new formatter uses as the description
                'weight' => 'Package weight in kilograms (between 0.1 and 50 kg). If unsure, use 1 kg.',
                'millat' => 'Millat is required and must be a number. If unsure, use 0.',
            ]
        );
        
        $this->authenticate();
        
        $payload = $this->mapper->mapRateToApi($rateRequest);
        
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/aladdin/api/v1/rates',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                'Failed to estimate rate',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        return $this->mapper->mapApiToRate($response['data'], $rateRequest);
    }
    
    public function getServiceTypes(): array
    {
        return [
            'same_day' => [
                'name' => 'Same Day Delivery',
                'sla' => 'Same day',
                'available' => true,
            ],
            'next_day' => [
                'name' => 'Next Day Delivery',
                'sla' => 'Next day',
                'available' => true,
            ],
            'standard' => [
                'name' => 'Standard Delivery',
                'sla' => '2-3 days',
                'available' => true,
            ],
        ];
    }
    
    // ==================== CodInterface ====================
    
    public function getCodDetails(string $trackingId): Cod
    {
        $this->authenticate();
        
        $response = $this->httpClient->get(
            $this->config->getApiUrl() . '/aladdin/api/v1/orders/' . $trackingId . '/cod',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                'Failed to get COD details',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        return $this->mapper->mapApiToCod($response['data']);
    }
    
    public function getCodLedger(array $filters = []): array
    {
        $this->authenticate();
        
        $response = $this->httpClient->get(
            $this->config->getApiUrl() . '/aladdin/api/v1/cod/ledger',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
                'query' => $filters,
            ]
        );
        
        if ($response['status'] !== 200) {
            throw new ApiException(
                'Failed to get COD ledger',
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data']
            );
        }
        
        $results = [];
        foreach ($response['data']['cod_records'] ?? [] as $codData) {
            $results[] = $this->mapper->mapApiToCod($codData);
        }
        
        return $results;
    }
    
    public function reconcileCod(array $trackingIds, array $settlementData = []): bool
    {
        $this->authenticate();
        
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/aladdin/api/v1/cod/reconcile',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'tracking_ids' => $trackingIds,
                    ...$settlementData,
                ],
            ]
        );
        
        return $response['status'] === 200 || $response['status'] === 201;
    }
    
    // ==================== WebhookInterface ====================
    
    public function registerWebhook(string $url, array $events = []): bool
    {
        $this->authenticate();
        
        $response = $this->httpClient->post(
            $this->config->getApiUrl() . '/aladdin/api/v1/webhooks',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'url' => $url,
                    'events' => $events ?: ['order.status.updated', 'order.delivered', 'order.returned'],
                ],
            ]
        );
        
        return $response['status'] === 200 || $response['status'] === 201;
    }
    
    public function unregisterWebhook(string $url): bool
    {
        $this->authenticate();
        
        $response = $this->httpClient->delete(
            $this->config->getApiUrl() . '/aladdin/api/v1/webhooks',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
                'json' => ['url' => $url],
            ]
        );
        
        return $response['status'] === 200 || $response['status'] === 204;
    }
    
    public function parseWebhook($payload): ?Tracking
    {
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }
        
        if (!is_array($payload)) {
            return null;
        }
        
        // Validate webhook signature if needed
        // $this->validateWebhookSignature($payload);
        
        return $this->mapper->mapApiToTracking($payload);
    }
    
    // ==================== Private Methods ====================
    
    /**
     * Authenticate and get access token.
     */
    private function authenticate(): void
    {
        if ($this->accessToken && !$this->isTokenExpired()) {
            return;
        }
        
        // Pathao API uses /aladdin/api/v1/issue-token endpoint
        $authEndpoint = $this->config->getAuthUrl() . '/aladdin/api/v1/issue-token';
        
        $response = $this->httpClient->post(
            $authEndpoint,
            [
                'json' => [
                    'client_id' => $this->config->getClientId(),
                    'client_secret' => $this->config->getClientSecret(),
                    'username' => $this->config->getUsername(),
                    'password' => $this->config->getPassword(),
                    'grant_type' => 'password',
                ],
            ]
        );
        
        // Check for error in response (Pathao returns 200 even for errors)
        $responseData = $response['data'] ?? [];
        if (isset($responseData['error']) && $responseData['error'] === true) {
            $errorMessage = $responseData['message'] ?? 'Failed to authenticate with Pathao';
            throw new ApiException(
                $errorMessage,
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $responseData
            );
        }
        
        if ($response['status'] !== 200) {
            $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'Failed to authenticate with Pathao';
            throw new ApiException(
                $errorMessage,
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $responseData
            );
        }
        
        // Pathao API returns token directly in response (not wrapped in data)
        $this->accessToken = $responseData['access_token'] ?? null;
        
        // Also store refresh token for future use
        if (isset($responseData['refresh_token'])) {
            // Could store this for token refresh functionality
        }
        
        if (!$this->accessToken) {
            throw new ApiException(
                'Invalid authentication response from Pathao. Token not found in response: ' . json_encode($responseData),
                0,
                null,
                'pathao',
                null,
                $responseData
            );
        }
    }
    
    /**
     * Check if token is expired (simplified - implement proper JWT parsing if needed).
     */
    private function isTokenExpired(): bool
    {
        // In a real implementation, decode JWT and check expiration
        // For now, assume token needs refresh
        return false;
    }
}
