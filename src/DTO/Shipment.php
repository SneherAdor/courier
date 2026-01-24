<?php

namespace Millat\DeshCourier\DTO;

/**
 * Data Transfer Object for shipment information.
 * 
 * This DTO normalizes shipment data across all couriers.
 */
class Shipment
{
    // Core shipment data
    public ?string $trackingId = null;
    public ?string $courierName = null;
    public ?string $externalOrderId = null; // Your system's order ID
    public ?string $orderSource = null; // 'facebook', 'website', 'pos', 'api'
    
    // Sender information
    public ?string $senderName = null;
    public ?string $senderPhone = null;
    public ?string $senderEmail = null;
    public ?string $senderAddress = null;
    public ?string $senderCity = null;
    public ?string $senderZone = null;
    public ?string $senderPostalCode = null;
    
    // Recipient information
    public ?string $recipientName = null;
    public ?string $recipientPhone = null;
    public ?string $recipientEmail = null;
    public ?string $recipientAddress = null;
    public ?string $recipientCity = null;
    public ?string $recipientZone = null;
    public ?string $recipientPostalCode = null;
    public ?string $recipientLandmark = null;
    
    // Shipment details
    public ?string $serviceType = 'standard'; // 'same_day', 'next_day', 'express', 'standard'
    public ?float $weight = null; // in kg
    public ?int $quantity = 1;
    public ?string $itemDescription = null;
    public ?float $itemValue = null; // Declared value
    
    // COD information
    public ?float $codAmount = null;
    public ?string $codType = null; // 'full', 'partial'
    
    // Delivery preferences
    public ?string $deliveryInstruction = null;
    public ?string $preferredDeliveryTime = null;
    
    // Status
    public ?string $status = null; // Normalized status
    public ?string $courierStatus = null; // Raw courier status
    
    // Metadata
    public ?string $labelUrl = null;
    public ?array $courierData = null; // Courier-specific data
    public ?\DateTimeInterface $createdAt = null;
    public ?\DateTimeInterface $updatedAt = null;
    
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
                // Handle DateTime conversion
                if (in_array($key, ['createdAt', 'updatedAt']) && is_string($value)) {
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
     * Validate required fields for shipment creation.
     */
    public function validateForCreation(): array
    {
        $errors = [];
        
        $required = [
            'recipientName',
            'recipientPhone',
            'recipientAddress',
            'recipientCity',
            'senderName',
            'senderPhone',
            'senderAddress',
            'senderCity',
        ];
        
        foreach ($required as $field) {
            if (empty($this->$field)) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        return $errors;
    }
}
