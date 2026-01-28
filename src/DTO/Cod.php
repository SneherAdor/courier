<?php

namespace Millat\DeshCourier\DTO;

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
    public ?string $status = null;
    public ?\DateTimeInterface $collectedAt = null;
    public ?string $collectionNote = null;
    
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }
    
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
    
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
    
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
