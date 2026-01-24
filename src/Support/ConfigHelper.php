<?php

namespace Millat\DeshCourier\Support;

/**
 * Configuration helper that works across plain PHP, Laravel, and WordPress.
 */
class ConfigHelper
{
    private array $config = [];
    
    public function __construct()
    {
        $this->loadFromEnvironment();
        $this->loadFromLaravel();
        $this->loadFromWordPress();
    }
    
    /**
     * Load configuration from environment variables.
     */
    private function loadFromEnvironment(): void
    {
        // Load from .env file if it exists
        $envFile = getcwd() . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\'');
                
                if (strpos($key, 'DESH_COURIER_') === 0) {
                    $configKey = strtolower(str_replace('DESH_COURIER_', '', $key));
                    $this->setNested($configKey, $value);
                }
            }
        }
        
        // Also check actual environment variables
        foreach ($_ENV as $key => $value) {
            if (strpos($key, 'DESH_COURIER_') === 0) {
                $configKey = strtolower(str_replace('DESH_COURIER_', '', $key));
                $this->setNested($configKey, $value);
            }
        }
    }
    
    /**
     * Load configuration from Laravel config.
     */
    private function loadFromLaravel(): void
    {
        if (function_exists('config')) {
            $laravelConfig = config('desh-courier');
            if (is_array($laravelConfig)) {
                $this->config = array_merge($this->config, $laravelConfig);
            }
        }
    }
    
    /**
     * Load configuration from WordPress constants.
     */
    private function loadFromWordPress(): void
    {
        if (defined('DESH_COURIER_CONFIG')) {
            $wpConfig = DESH_COURIER_CONFIG;
            if (is_array($wpConfig)) {
                $this->config = array_merge($this->config, $wpConfig);
            }
        }
    }
    
    /**
     * Set nested config value (e.g., 'pathao.client_id' => 'value').
     */
    private function setNested(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }
    
    /**
     * Get configuration value.
     * 
     * @param string|null $key Dot-notation key (e.g., 'pathao.client_id')
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Check if configuration key exists.
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }
        
        return true;
    }
    
    /**
     * Set configuration value.
     */
    public function set(string $key, $value): void
    {
        $this->setNested($key, $value);
    }
}
