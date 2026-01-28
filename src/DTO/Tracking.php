<?php

namespace Millat\DeshCourier\DTO;

class Tracking
{
    public ?string $trackingId = null;
    public ?string $courierName = null;
    public ?string $status = null;
    public ?string $courierStatus = null;
    public ?string $statusDescription = null;
    
    public ?string $currentLocation = null;
    public ?string $destinationCity = null;
    
    public ?\DateTimeInterface $pickedAt = null;
    public ?\DateTimeInterface $inTransitAt = null;
    public ?\DateTimeInterface $outForDeliveryAt = null;
    public ?\DateTimeInterface $deliveredAt = null;
    public ?\DateTimeInterface $returnedAt = null;
    public ?\DateTimeInterface $lastUpdatedAt = null;
    
    public ?string $deliveredTo = null;
    public ?string $deliveryNote = null;
    public ?string $deliveryAttempt = null;
    
    public ?float $codAmount = null;
    public ?float $codCollected = null;
    public ?bool $codSettled = null;
    
    public ?array $history = null;
    
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }
    
    public function fill(array $data): self
    {
        $dateFields = [
            'pickedAt', 'inTransitAt', 'outForDeliveryAt',
            'deliveredAt', 'returnedAt', 'lastUpdatedAt'
        ];
        
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
    
    public function isDelivered(): bool
    {
        return $this->status === 'DELIVERED';
    }
    
    public function isReturned(): bool
    {
        return $this->status === 'RETURNED';
    }
    
    public function isInTransit(): bool
    {
        return in_array($this->status, ['PICKED', 'IN_TRANSIT', 'OUT_FOR_DELIVERY']);
    }
}
