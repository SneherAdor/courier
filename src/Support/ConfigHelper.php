<?php

namespace Millat\DeshCourier\Support;

class ConfigHelper
{
    private array $config = [];
    
    public function __construct()
    {
        $this->loadFromEnvironment();
        $this->loadFromLaravel();
        $this->loadFromWordPress();
    }
    
    private function loadFromEnvironment(): void
    {
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
        
        foreach ($_ENV as $key => $value) {
            if (strpos($key, 'DESH_COURIER_') === 0) {
                $configKey = strtolower(str_replace('DESH_COURIER_', '', $key));
                $this->setNested($configKey, $value);
            }
        }
    }
    
    private function loadFromLaravel(): void
    {
        if (function_exists('config')) {
            $laravelConfig = config('desh-courier');
            if (is_array($laravelConfig)) {
                $this->config = array_merge($this->config, $laravelConfig);
            }
        }
    }
    
    private function loadFromWordPress(): void
    {
        if (defined('DESH_COURIER_CONFIG')) {
            $wpConfig = DESH_COURIER_CONFIG;
            if (is_array($wpConfig)) {
                $this->config = array_merge($this->config, $wpConfig);
            }
        }
    }
    
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
    
    public function set(string $key, $value): void
    {
        $this->setNested($key, $value);
    }
}
