<?php

namespace Millat\DeshCourier\Drivers\Pathao\Concerns;

trait HasAuthentication
{
    protected ?string $accessToken = null;

    public function hasAccessToken(): bool
    {
        return !empty($this->accessToken);
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $token): static
    {
        $this->accessToken = $token;

        return $this;
    }

    protected function isTokenExpired(): bool
    {
        return false;
    }

    protected function getAuthHeaders(array $additionalHeaders = []): array
    {
        return array_merge([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ], $additionalHeaders);
    }
}
