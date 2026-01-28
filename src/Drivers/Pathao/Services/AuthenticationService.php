<?php

namespace Millat\DeshCourier\Drivers\Pathao\Services;

use Millat\DeshCourier\Drivers\Pathao\PathaoConfig;
use Millat\DeshCourier\Support\HttpClient;
use Millat\DeshCourier\Exceptions\ApiException;

class AuthenticationService
{
    public function __construct(
        protected PathaoConfig $config,
        protected HttpClient $httpClient
    ) {}

    public function authenticate(): string
    {
        $response = $this->httpClient->post(
            $this->getAuthEndpoint(),
            [
                'json' => $this->getAuthPayload(),
            ]
        );

        return $this->extractToken($response);
    }

    protected function getAuthEndpoint(): string
    {
        return rtrim($this->config->getAuthUrl(), '/') . '/aladdin/api/v1/issue-token';
    }

    protected function getAuthPayload(): array
    {
        return [
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
            'grant_type' => 'password',
        ];
    }

    protected function extractToken(array $response): string
    {
        $responseData = $response['data'] ?? [];

        if (isset($responseData['error']) && $responseData['error'] === true) {
            throw $this->createAuthException(
                $responseData['message'] ?? 'Failed to authenticate with Pathao',
                $response['status'] ?? 0,
                $responseData
            );
        }

        if (($response['status'] ?? 0) !== 200) {
            throw $this->createAuthException(
                $responseData['message'] ?? $responseData['error'] ?? 'Failed to authenticate with Pathao',
                $response['status'] ?? 0,
                $responseData
            );
        }

        $token = $responseData['access_token'] ?? null;

        if (!$token) {
            throw $this->createAuthException(
                'Invalid authentication response from Pathao. Token not found in response: ' . json_encode($responseData),
                0,
                $responseData
            );
        }

        return $token;
    }

    protected function createAuthException(string $message, int $statusCode, array $responseData): ApiException
    {
        return new ApiException(
            $message,
            $statusCode,
            null,
            'pathao',
            $statusCode,
            $responseData
        );
    }
}
