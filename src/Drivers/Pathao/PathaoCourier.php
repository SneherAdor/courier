<?php

namespace Millat\DeshCourier\Drivers\Pathao;

use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\Contracts\TrackingInterface;
use Millat\DeshCourier\Contracts\RateInterface;
use Millat\DeshCourier\Contracts\CodInterface;
use Millat\DeshCourier\Contracts\WebhookInterface;
use Millat\DeshCourier\Contracts\StoreInterface;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Support\HttpClient;
use Millat\DeshCourier\Exceptions\InvalidConfigurationException;
use Millat\DeshCourier\Drivers\Pathao\Handlers\ShipmentHandler;
use Millat\DeshCourier\Drivers\Pathao\Handlers\TrackingHandler;
use Millat\DeshCourier\Drivers\Pathao\Handlers\RateHandler;
use Millat\DeshCourier\Drivers\Pathao\Handlers\CodHandler;
use Millat\DeshCourier\Drivers\Pathao\Handlers\WebhookHandler;
use Millat\DeshCourier\Drivers\Pathao\Handlers\MetadataHandler;
use Millat\DeshCourier\Drivers\Pathao\Handlers\StoreHandler;
use Millat\DeshCourier\Drivers\Pathao\Services\AuthenticationService;
use Millat\DeshCourier\Drivers\Pathao\Concerns\HasAuthentication;

class PathaoCourier implements
    CourierInterface,
    ShipmentInterface,
    TrackingInterface,
    RateInterface,
    CodInterface,
    WebhookInterface,
    StoreInterface
{
    use HasAuthentication;

    protected PathaoConfig $config;
    protected HttpClient $httpClient;
    protected PathaoMapper $mapper;
    protected AuthenticationService $authService;
    protected ShipmentHandler $shipmentHandler;
    protected TrackingHandler $trackingHandler;
    protected RateHandler $rateHandler;
    protected CodHandler $codHandler;
    protected WebhookHandler $webhookHandler;
    protected MetadataHandler $metadataHandler;
    protected StoreHandler $storeHandler;

    public function __construct(PathaoConfig $config, ?HttpClient $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? new HttpClient();
        $this->mapper = new PathaoMapper();
        $this->authService = new AuthenticationService($this->config, $this->httpClient);

        $this->validateConfiguration();
        $this->initializeHandlers();
    }

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
            'store.list',
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

    public function createShipment(Shipment|array $shipment): Shipment
    {
        return $this->withAuthentication(fn() => $this->shipmentHandler->create($shipment));
    }

    public function updateShipment(string $trackingId, Shipment|array $shipment): Shipment
    {
        return $this->withAuthentication(fn() => $this->shipmentHandler->update($trackingId, $shipment));
    }

    public function cancelShipment(string $trackingId, ?string $reason = null): bool
    {
        return $this->withAuthentication(fn() => $this->shipmentHandler->cancel($trackingId, $reason));
    }

    public function createBulkShipments(array $shipments): array
    {
        return $this->withAuthentication(fn() => $this->shipmentHandler->createBulk($shipments));
    }

    public function generateLabel(string $trackingId, string $format = 'pdf'): string
    {
        return $this->withAuthentication(fn() => $this->shipmentHandler->generateLabel($trackingId, $format));
    }

    public function requestPickup(string $trackingId, array $pickupDetails = []): bool
    {
        return $this->withAuthentication(fn() => $this->shipmentHandler->requestPickup($trackingId, $pickupDetails));
    }

    public function track(string $trackingId): Tracking
    {
        return $this->withAuthentication(fn() => $this->trackingHandler->track($trackingId));
    }

    public function trackBulk(array $trackingIds): array
    {
        return $this->withAuthentication(fn() => $this->trackingHandler->trackBulk($trackingIds));
    }

    public function getStatus(string $trackingId): string
    {
        return $this->withAuthentication(fn() => $this->trackingHandler->getStatus($trackingId));
    }

    public function estimateRate(Rate|array $rateRequest): Rate
    {
        return $this->withAuthentication(fn() => $this->rateHandler->estimate($rateRequest));
    }

    public function getServiceTypes(): array
    {
        return $this->rateHandler->getServiceTypes();
    }

    public function getCodDetails(string $trackingId): Cod
    {
        return $this->withAuthentication(fn() => $this->codHandler->getDetails($trackingId));
    }

    public function getCodLedger(array $filters = []): array
    {
        return $this->withAuthentication(fn() => $this->codHandler->getLedger($filters));
    }

    public function reconcileCod(array $trackingIds, array $settlementData = []): bool
    {
        return $this->withAuthentication(fn() => $this->codHandler->reconcile($trackingIds, $settlementData));
    }

    public function registerWebhook(string $url, array $events = []): bool
    {
        return $this->withAuthentication(fn() => $this->webhookHandler->register($url, $events));
    }

    public function unregisterWebhook(string $url): bool
    {
        return $this->withAuthentication(fn() => $this->webhookHandler->unregister($url));
    }

    public function parseWebhook($payload): ?Tracking
    {
        return $this->webhookHandler->parse($payload);
    }

    public function getStores(array $filters = []): array
    {
        return $this->withAuthentication(fn() => $this->storeHandler->getStores($filters));
    }

    public function getStore(int $storeId): ?array
    {
        return $this->withAuthentication(fn() => $this->storeHandler->getStore($storeId));
    }

    public function getDefaultStore(): ?array
    {
        return $this->withAuthentication(fn() => $this->storeHandler->getDefaultStore());
    }

    protected function validateConfiguration(): void
    {
        if (empty($this->config->getClientId()) || empty($this->config->getClientSecret())) {
            throw new InvalidConfigurationException(
                "Pathao configuration is incomplete. Client ID and Secret are required.",
                0,
                null,
                'pathao'
            );
        }
    }

    protected function initializeHandlers(): void
    {
        $this->shipmentHandler = new ShipmentHandler($this->config, $this->httpClient, $this->mapper);
        $this->trackingHandler = new TrackingHandler($this->config, $this->httpClient, $this->mapper);
        $this->rateHandler = new RateHandler($this->config, $this->httpClient, $this->mapper);
        $this->codHandler = new CodHandler($this->config, $this->httpClient, $this->mapper);
        $this->webhookHandler = new WebhookHandler($this->config, $this->httpClient, $this->mapper);
        $this->metadataHandler = new MetadataHandler($this->config, $this->httpClient, $this->mapper);
        $this->storeHandler = new StoreHandler($this->config, $this->httpClient, $this->mapper);
    }

    protected function withAuthentication(\Closure $callback): mixed
    {
        $this->authenticate();
        $this->syncTokenToHandlers();
        
        return $callback();
    }

    protected function authenticate(): void
    {
        if ($this->hasAccessToken() && !$this->isTokenExpired()) {
            return;
        }

        $this->setAccessToken($this->authService->authenticate());
    }

    protected function syncTokenToHandlers(): void
    {
        $token = $this->getAccessToken();

        $this->shipmentHandler->setAccessToken($token);
        $this->trackingHandler->setAccessToken($token);
        $this->rateHandler->setAccessToken($token);
        $this->codHandler->setAccessToken($token);
        $this->webhookHandler->setAccessToken($token);
        $this->metadataHandler->setAccessToken($token);
        $this->storeHandler->setAccessToken($token);
    }
}
