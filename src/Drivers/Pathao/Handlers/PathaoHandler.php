<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\Drivers\Pathao\PathaoConfig;
use Millat\DeshCourier\Drivers\Pathao\PathaoMapper;
use Millat\DeshCourier\Drivers\Pathao\Concerns\HasAuthentication;
use Millat\DeshCourier\Drivers\Pathao\Concerns\HttpRequest;
use Millat\DeshCourier\Support\HttpClient;
use Millat\DeshCourier\Support\Validate;

abstract class PathaoHandler
{
    use HasAuthentication, HttpRequest, Validate;

    public function __construct(
        protected PathaoConfig $config,
        protected HttpClient $httpClient,
        protected PathaoMapper $mapper
    ) {}

    protected function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    protected function getConfig(): PathaoConfig
    {
        return $this->config;
    }

    protected function getMapper(): PathaoMapper
    {
        return $this->mapper;
    }

    protected function ensureAuthenticated(): void
    {
        if (!$this->hasAccessToken()) {
            throw new \RuntimeException('Access token is required. Please authenticate first.');
        }
    }
}
