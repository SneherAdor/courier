<?php

namespace Millat\DeshCourier\Tests\Unit\DTO;

use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\Tests\TestCase;

class ShipmentTest extends TestCase
{
    /**
     * Test creating empty DTO.
     */
    public function testCreateEmptyDto(): void
    {
        $shipment = new Shipment();
        
        $this->assertInstanceOf(Shipment::class, $shipment);
        $this->assertNull($shipment->trackingId);
    }
    
    /**
     * Test creating DTO from array.
     */
    public function testFromArrayStaticMethod(): void
    {
        $data = [
            'trackingId' => 'TRACK123',
            'recipientName' => 'John Doe',
            'recipientPhone' => '01712345678',
            'recipientAddress' => 'House 123, Road 45',
            'recipientCity' => 'Dhaka',
            'senderName' => 'Store Name',
            'senderPhone' => '01787654321',
            'senderAddress' => 'Shop 456',
            'senderCity' => 'Dhaka',
            'weight' => 1.5,
            'codAmount' => 1500,
            'serviceType' => 'next_day',
        ];
        
        $shipment = Shipment::fromArray($data);
        
        $this->assertEquals('TRACK123', $shipment->trackingId);
        $this->assertEquals('John Doe', $shipment->recipientName);
        $this->assertEquals('01712345678', $shipment->recipientPhone);
        $this->assertEquals(1.5, $shipment->weight);
        $this->assertEquals(1500, $shipment->codAmount);
        $this->assertEquals('next_day', $shipment->serviceType);
    }
    
    /**
     * Test converting DTO to array.
     */
    public function testToArray(): void
    {
        $shipment = new Shipment();
        $shipment->trackingId = 'TRACK123';
        $shipment->recipientName = 'John Doe';
        $shipment->weight = 1.5;
        $shipment->codAmount = 1500;
        
        $array = $shipment->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('TRACK123', $array['trackingId']);
        $this->assertEquals('John Doe', $array['recipientName']);
        $this->assertEquals(1.5, $array['weight']);
        $this->assertEquals(1500, $array['codAmount']);
    }
    
    /**
     * Test validation for creation.
     */
    public function testValidateForCreationWithValidData(): void
    {
        $shipment = new Shipment();
        $shipment->recipientName = 'John Doe';
        $shipment->recipientPhone = '01712345678';
        $shipment->recipientAddress = 'House 123';
        $shipment->recipientCity = 'Dhaka';
        $shipment->senderName = 'Store';
        $shipment->senderPhone = '01787654321';
        $shipment->senderAddress = 'Shop 456';
        $shipment->senderCity = 'Dhaka';
        
        $errors = $shipment->validateForCreation();
        
        $this->assertEmpty($errors);
    }
    
    /**
     * Test validation for creation with missing fields.
     */
    public function testValidateForCreationWithMissingFields(): void
    {
        $shipment = new Shipment();
        $shipment->recipientName = 'John Doe';
        // Missing required fields
        
        $errors = $shipment->validateForCreation();
        
        $this->assertNotEmpty($errors);
        $this->assertContains("Field 'recipientPhone' is required", $errors);
        $this->assertContains("Field 'recipientAddress' is required", $errors);
        $this->assertContains("Field 'senderName' is required", $errors);
    }
    
    /**
     * Test DateTime conversion in fromArray.
     */
    public function testFromArrayWithDateTime(): void
    {
        $data = [
            'createdAt' => '2024-01-15 10:30:00',
            'updatedAt' => '2024-01-15 11:00:00',
        ];
        
        $shipment = Shipment::fromArray($data);
        
        $this->assertInstanceOf(\DateTimeInterface::class, $shipment->createdAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $shipment->updatedAt);
        $this->assertEquals('2024-01-15 10:30:00', $shipment->createdAt->format('Y-m-d H:i:s'));
    }
    
    /**
     * Test DateTime conversion in toArray.
     */
    public function testToArrayWithDateTime(): void
    {
        $shipment = new Shipment();
        $shipment->createdAt = new \DateTimeImmutable('2024-01-15 10:30:00');
        
        $array = $shipment->toArray();
        
        $this->assertEquals('2024-01-15 10:30:00', $array['createdAt']);
    }
}
