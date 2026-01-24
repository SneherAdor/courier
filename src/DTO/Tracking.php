<?php

namespace Millat\DeshCourier\DTO;

/**
 * Data Transfer Object for tracking information.
 */
class Tracking
{
    public ?string $trackingId = null;
    public ?string $courierName = null;
    public ?string $status = null; // Normalized status
    public ?string $courierStatus = null; // Raw courier status
    public ?string $statusDescription = null;
    
    // Location information
    public ?string $currentLocation = null;
    public ?string $destinationCity = null;
    
    // Timestamps
    public ?\DateTimeInterface $pickedAt = null;
    public ?\DateTimeInterface $inTransitAt = null;
    public ?\DateTimeInterface $outForDeliveryAt = null;
    public ?\DateTimeInterface $deliveredAt = null;
    public ?\DateTimeInterface $returnedAt = null;
    public ?\DateTimeInterface $lastUpdatedAt = null;
    
    // Delivery information
    public ?string $deliveredTo = null; // Person who received
    public ?string $deliveryNote = null;
    public ?string $deliveryAttempt = null; // Attempt number
    
    // COD information
    public ?float $codAmount = null;
    public ?float $codCollected = null;
    public ?bool $codSettled = null;
    
    // Tracking history
    public ?array $history = null; // Array of status updates
    
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
    
    /**
     * Check if shipment is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'DELIVERED';
    }
    
    /**
     * Check if shipment is returned.
     */
    public function isReturned(): bool
    {
        return $this->status === 'RETURNED';
    }
    
    /**
     * Check if shipment is in transit.
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, ['PICKED', 'IN_TRANSIT', 'OUT_FOR_DELIVERY']);
    }
}
