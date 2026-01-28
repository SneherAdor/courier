<?php

namespace Millat\DeshCourier\Drivers\Pathao\Concerns;

use Millat\DeshCourier\Support\HttpClient;
use Millat\DeshCourier\Drivers\Pathao\PathaoConfig;

trait HttpRequest
{
    protected function get(string $endpoint, array $options = []): array
    {
        return $this->request('get', $endpoint, $options);
    }

    protected function post(string $endpoint, array $options = []): array
    {
        return $this->request('post', $endpoint, $options);
    }

    protected function put(string $endpoint, array $options = []): array
    {
        return $this->request('put', $endpoint, $options);
    }

    protected function delete(string $endpoint, array $options = []): array
    {
        return $this->request('delete', $endpoint, $options);
    }

    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $this->ensureAuthenticated();
        
        $url = $this->buildUrl($endpoint);
        
        $options['headers'] = array_merge(
            $this->getAuthHeaders(),
            $options['headers'] ?? []
        );

        return $this->getHttpClient()->{$method}($url, $options);
    }

    protected function buildUrl(string $endpoint): string
    {
        $baseUrl = $this->getConfig()->getApiUrl();
        
        return rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    abstract protected function getHttpClient(): HttpClient;

    abstract protected function getConfig(): PathaoConfig;

    abstract protected function getAuthHeaders(array $additionalHeaders = []): array;
}
