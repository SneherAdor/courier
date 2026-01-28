<?php

namespace Millat\DeshCourier\Contracts;

use Millat\DeshCourier\DTO\Tracking;

interface WebhookInterface
{
    public function registerWebhook(string $url, array $events = []): bool;

    public function unregisterWebhook(string $url): bool;

    public function parseWebhook($payload): ?Tracking;
}
