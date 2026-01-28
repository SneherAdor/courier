<?php

namespace Millat\DeshCourier\DTO;

class Shipment
{
    public ?string $trackingId = null;
    public ?string $courierName = null;
    public ?string $externalOrderId = null;
    public ?string $orderSource = null;
    
    public ?string $senderName = null;
    public ?string $senderPhone = null;
    public ?string $senderEmail = null;
    public ?string $senderAddress = null;
    public ?string $senderCity = null;
    public ?string $senderZone = null;
    public ?string $senderPostalCode = null;
    
    public ?string $recipientName = null;
    public ?string $recipientPhone = null;
    public ?string $recipientEmail = null;
    public ?string $recipientAddress = null;
    public ?string $recipientCity = null;
    public ?string $recipientZone = null;
    public ?string $recipientPostalCode = null;
    public ?string $recipientLandmark = null;
    
    public ?string $serviceType = 'standard';
    public ?float $weight = null;
    public ?int $quantity = 1;
    public ?string $itemDescription = null;
    public ?float $itemValue = null;
    
    public ?float $codAmount = null;
    public ?string $codType = null;
    
    public ?string $deliveryInstruction = null;
    public ?string $preferredDeliveryTime = null;
    
    public ?string $status = null;
    public ?string $courierStatus = null;
    
    public ?string $labelUrl = null;
    public ?array $courierData = null;
    public ?\DateTimeInterface $createdAt = null;
    public ?\DateTimeInterface $updatedAt = null;
    
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
                if (in_array($key, ['createdAt', 'updatedAt']) && is_string($value)) {
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
