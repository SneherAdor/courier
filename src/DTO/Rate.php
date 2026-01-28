<?php

namespace Millat\DeshCourier\DTO;

class Rate
{
    public ?string $fromCity = null;
    public ?string $fromZone = null;
    public ?string $toCity = null;
    public ?string $toZone = null;
    public ?float $weight = null;
    public ?string $serviceType = null;
    public ?float $codAmount = null;
    public ?float $itemValue = null;
    
    public ?float $deliveryCharge = null;
    public ?float $codCharge = null;
    public ?float $totalCharge = null;
    public ?string $currency = 'BDT';
    public ?int $estimatedDays = null;
    public ?\DateTimeInterface $estimatedDeliveryDate = null;
    
    public ?array $breakdown = null;
    public ?string $courierName = null;
    
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }
    
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
