<?php

namespace Millat\DeshCourier\DTO;

/**
 * Data Transfer Object for rate estimation.
 */
class Rate
{
    // Request fields
    public ?string $fromCity = null;
    public ?string $fromZone = null;
    public ?string $toCity = null;
    public ?string $toZone = null;
    public ?float $weight = null; // in kg
    public ?string $serviceType = null;
    public ?float $codAmount = null;
    public ?float $itemValue = null;
    
    // Response fields
    public ?float $deliveryCharge = null;
    public ?float $codCharge = null;
    public ?float $totalCharge = null;
    public ?string $currency = 'BDT';
    public ?int $estimatedDays = null;
    public ?\DateTimeInterface $estimatedDeliveryDate = null;
    
    // Additional information
    public ?array $breakdown = null; // Detailed charge breakdown
    public ?string $courierName = null;
    
    /**
     * Constructor with optional mass assignment from array.
     * 
     * @param array<string, mixed> $data Optional array of data to populate properties
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }
    
    /**
     * Fill properties from array.
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public function fill(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if ($key === 'estimatedDeliveryDate' && is_string($value)) {
                    $this->$key = new \DateTimeImmutable($value);
                } else {
                    $this->$key = $value;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Create from array (static factory method).
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
    
    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $data = [];
        
        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            } else {
                $data[$key] = $value;
            }
        }
        
        return $data;
    }
}
