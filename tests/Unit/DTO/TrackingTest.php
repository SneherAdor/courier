<?php

namespace Millat\DeshCourier\Tests\Unit\DTO;

use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\Core\StatusMapper;
use Millat\DeshCourier\Tests\TestCase;

class TrackingTest extends TestCase
{
    /**
     * Test creating DTO from array.
     */
    public function testFromArray(): void
    {
        $data = [
            'trackingId' => 'TRACK123',
            'status' => StatusMapper::DELIVERED,
            'currentLocation' => 'Dhaka',
            'codAmount' => 1500,
            'codCollected' => 1500,
        ];
        
        $tracking = Tracking::fromArray($data);
        
        $this->assertEquals('TRACK123', $tracking->trackingId);
        $this->assertEquals(StatusMapper::DELIVERED, $tracking->status);
        $this->assertEquals('Dhaka', $tracking->currentLocation);
        $this->assertEquals(1500, $tracking->codAmount);
        $this->assertEquals(1500, $tracking->codCollected);
    }
    
    /**
     * Test isDelivered method.
     */
    public function testIsDelivered(): void
    {
        $tracking = new Tracking();
        $tracking->status = StatusMapper::DELIVERED;
        
        $this->assertTrue($tracking->isDelivered());
        
        $tracking->status = StatusMapper::IN_TRANSIT;
        $this->assertFalse($tracking->isDelivered());
    }
    
    /**
     * Test isReturned method.
     */
    public function testIsReturned(): void
    {
        $tracking = new Tracking();
        $tracking->status = StatusMapper::RETURNED;
        
        $this->assertTrue($tracking->isReturned());
        
        $tracking->status = StatusMapper::DELIVERED;
        $this->assertFalse($tracking->isReturned());
    }
    
    /**
     * Test isInTransit method.
     */
    public function testIsInTransit(): void
    {
        $tracking = new Tracking();
        
        $tracking->status = StatusMapper::PICKED;
        $this->assertTrue($tracking->isInTransit());
        
        $tracking->status = StatusMapper::IN_TRANSIT;
        $this->assertTrue($tracking->isInTransit());
        
        $tracking->status = StatusMapper::OUT_FOR_DELIVERY;
        $this->assertTrue($tracking->isInTransit());
        
        $tracking->status = StatusMapper::DELIVERED;
        $this->assertFalse($tracking->isInTransit());
    }
    
    /**
     * Test DateTime conversion.
     */
    public function testDateTimeConversion(): void
    {
        $data = [
            'deliveredAt' => '2024-01-15 10:30:00',
            'pickedAt' => '2024-01-14 09:00:00',
        ];
        
        $tracking = Tracking::fromArray($data);
        
        $this->assertInstanceOf(\DateTimeInterface::class, $tracking->deliveredAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $tracking->pickedAt);
    }
}
