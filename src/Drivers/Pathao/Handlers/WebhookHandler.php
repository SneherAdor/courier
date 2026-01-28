<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\DTO\Tracking;

class WebhookHandler extends PathaoHandler
{
    public function register(string $url, array $events = []): bool
    {
        $response = $this->post('aladdin/api/v1/webhooks', [
            'json' => [
                'url' => $url,
                'events' => $events ?: $this->getDefaultEvents(),
            ],
        ]);
        
        return in_array($response['status'], [200, 201]);
    }
    
    public function unregister(string $url): bool
    {
        $response = $this->delete('aladdin/api/v1/webhooks', [
            'json' => ['url' => $url],
        ]);
        
        return in_array($response['status'], [200, 204]);
    }
    
    public function parse($payload): ?Tracking
    {
        $payload = $this->normalizePayload($payload);
        
        if (!is_array($payload)) {
            return null;
        }
        
        return $this->getMapper()->mapApiToTracking($payload);
    }
    
    protected function getDefaultEvents(): array
    {
        return [
            'order.status.updated',
            'order.delivered',
            'order.returned',
        ];
    }
    
    protected function normalizePayload($payload): ?array
    {
        if (is_string($payload)) {
            return json_decode($payload, true);
        }
        
        return is_array($payload) ? $payload : null;
    }
}
