<?php

namespace Millat\DeshCourier\Drivers\Pathao;

/**
 * Configuration class for Pathao courier.
 */
class PathaoConfig
{
    private string $clientId;
    private string $clientSecret;
    private ?string $username = null;
    private ?string $password = null;
    private string $apiUrl;
    private string $authUrl;
    private ?string $environment = null; // 'sandbox' or 'production'
    
    public function __construct(array $config = [])
    {
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->username = $config['username'] ?? null;
        $this->password = $config['password'] ?? null;
        $this->environment = $config['environment'] ?? 'production';
        
        // Set API URLs based on environment
        if ($this->environment === 'sandbox') {
            $this->apiUrl = $config['api_url'] ?? 'https://courier-api-sandbox.pathao.com';
            $this->authUrl = $config['auth_url'] ?? 'https://courier-api-sandbox.pathao.com';
        } else {
            $this->apiUrl = $config['api_url'] ?? 'https://api-hermes.pathao.com';
            $this->authUrl = $config['auth_url'] ?? 'https://api-hermes.pathao.com';
        }
    }
    
    public function getClientId(): string
    {
        return $this->clientId;
    }
    
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }
    
    public function getUsername(): ?string
    {
        return $this->username;
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
    
    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }
    
    public function getEnvironment(): ?string
    {
        return $this->environment;
    }
}
