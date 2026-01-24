<?php

namespace Millat\DeshCourier\DTO;

/**
 * Data Transfer Object for COD information.
 */
class Cod
{
    public ?string $trackingId = null;
    public ?string $courierName = null;
    public ?float $codAmount = null;
    public ?float $codCollected = null;
    public ?float $codPending = null;
    public ?bool $isSettled = null;
    public ?\DateTimeInterface $settledAt = null;
    public ?string $settlementReference = null;
    public ?string $status = null; // 'pending', 'collected', 'settled', 'failed'
    public ?\DateTimeInterface $collectedAt = null;
    public ?string $collectionNote = null;
    
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
        $dateFields = ['settledAt', 'collectedAt'];
        
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if (in_array($key, $dateFields) && is_string($value)) {
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
