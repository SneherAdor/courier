<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Tracking;

/**
 * Interface for courier drivers that support webhooks.
 */
interface WebhookInterface
{
    /**
     * Register a webhook URL with the courier.
     * 
     * @param string $url Webhook endpoint URL
     * @param array<string> $events Events to subscribe to
     * @return bool
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function registerWebhook(string $url, array $events = []): bool;

    /**
     * Unregister a webhook.
     * 
     * @param string $url
     * @return bool
     * @throws \Millat\DeshCourier\Exceptions\CourierException
     */
    public function unregisterWebhook(string $url): bool;

    /**
     * Validate and parse an incoming webhook payload.
     * 
     * @param array<string, mixed>|string $payload Raw webhook data
     * @return Tracking|null Parsed tracking data, or null if invalid
     */
    public function parseWebhook($payload): ?Tracking;
}
