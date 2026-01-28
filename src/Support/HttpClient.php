<?php

namespace Millat\DeshCourier\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HttpClient
{
    private Client $client;
    private ?LoggerInterface $logger;
    private int $maxRetries;
    private int $retryDelay;
    
    public function __construct(
        ?Client $client = null,
        ?LoggerInterface $logger = null,
        int $maxRetries = 3,
        int $retryDelay = 1000
    ) {
        $this->client = $client ?? new Client();
        $this->logger = $logger ?? new NullLogger();
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $retryDelay;
    }
    
    public function get(string $url, array $options = []): array
    {
        return $this->request('GET', $url, $options);
    }
    
    public function post(string $url, array $options = []): array
    {
        return $this->request('POST', $url, $options);
    }
    
    public function put(string $url, array $options = []): array
    {
        return $this->request('PUT', $url, $options);
    }
    
    public function delete(string $url, array $options = []): array
    {
        return $this->request('DELETE', $url, $options);
    }
    
    public function request(string $method, string $url, array $options = []): array
    {
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $this->maxRetries) {
            try {
                $this->logger->debug("HTTP Request: {$method} {$url}", [
                    'attempt' => $attempt + 1,
                    'options' => $this->sanitizeOptions($options),
                ]);
                
                $response = $this->client->request($method, $url, $options);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $data = ['raw' => $body];
                }
                
                $this->logger->debug("HTTP Response", [
                    'status' => $response->getStatusCode(),
                    'data' => $data,
                ]);
                
                return [
                    'status' => $response->getStatusCode(),
                    'data' => $data,
                    'headers' => $response->getHeaders(),
                ];
                
            } catch (GuzzleException $e) {
                $lastException = $e;
                $attempt++;
                
                $this->logger->warning("HTTP Request failed", [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
                
                // Don't retry on 4xx errors (client errors)
                if ($e->hasResponse() && $e->getResponse()->getStatusCode() < 500) {
                    throw $e;
                }
                
                if ($attempt < $this->maxRetries) {
                    usleep($this->retryDelay * 1000 * $attempt);
                }
            }
        }
        
        throw $lastException ?? new \RuntimeException("Request failed after {$this->maxRetries} attempts");
    }
    
    private function sanitizeOptions(array $options): array
    {
        $sensitive = ['headers', 'auth', 'password', 'token', 'api_key'];
        $sanitized = $options;
        
        foreach ($sensitive as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '***REDACTED***';
            }
        }
        
        return $sanitized;
    }
}
