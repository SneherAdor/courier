<?php

namespace Millat\DeshCourier\Tests\Unit\Drivers\Pathao;

use Millat\DeshCourier\Drivers\Pathao\PathaoMapper;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\DTO\Tracking;
use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\DTO\Cod;
use Millat\DeshCourier\Core\StatusMapper;
use Millat\DeshCourier\Tests\TestCase;

class PathaoMapperTest extends TestCase
{
    private PathaoMapper $mapper;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new PathaoMapper();
    }
    
    /**
     * Test mapping Shipment to Pathao API format.
     */
    public function testMapShipmentToApi(): void
    {
        $shipment = new Shipment();
        $shipment->externalOrderId = 'ORDER123';
        $shipment->recipientName = 'John Doe';
        $shipment->recipientPhone = '01712345678';
        $shipment->recipientAddress = 'House 123, Road 45';
        $shipment->recipientCity = '1'; // City ID
        $shipment->recipientZone = '2'; // Zone ID
        $shipment->weight = 1.5;
        $shipment->codAmount = 1500;
        $shipment->serviceType = 'standard';
        $shipment->itemDescription = 'Test item';
        $shipment->deliveryInstruction = 'Call before delivery';
        $shipment->courierData = ['store_id' => 12345];
        
        $apiData = $this->mapper->mapShipmentToApi($shipment);
        
        $this->assertEquals(12345, $apiData['store_id']);
        $this->assertEquals('ORDER123', $apiData['merchant_order_id']);
        $this->assertEquals('John Doe', $apiData['recipient_name']);
        $this->assertEquals('01712345678', $apiData['recipient_phone']);
        $this->assertEquals(48, $apiData['delivery_type']); // standard = 48
        $this->assertEquals(2, $apiData['item_type']); // Parcel
        $this->assertEquals('1.5', $apiData['item_weight']); // String format
        $this->assertEquals(1500, $apiData['amount_to_collect']);
        $this->assertEquals(1, $apiData['item_quantity']);
    }
    
    /**
     * Test mapping service types.
     */
    public function testMapServiceType(): void
    {
        $shipment = new Shipment();
        $shipment->serviceType = 'same_day';
        $shipment->courierData = ['store_id' => 12345];
        $shipment->recipientName = 'Test';
        $shipment->recipientPhone = '01712345678';
        $shipment->recipientAddress = 'Test Address';
        $shipment->senderName = 'Store';
        $shipment->senderPhone = '01787654321';
        $shipment->senderAddress = 'Store Address';
        
        $apiData = $this->mapper->mapShipmentToApi($shipment);
        $this->assertEquals(12, $apiData['delivery_type']); // same_day = 12 (On Demand)
        
        $shipment->serviceType = 'next_day';
        $apiData = $this->mapper->mapShipmentToApi($shipment);
        $this->assertEquals(48, $apiData['delivery_type']); // next_day = 48 (Normal)
    }
    
    /**
     * Test mapping API response to Shipment.
     */
    public function testMapApiToShipment(): void
    {
        $apiData = [
            'data' => [
                'consignment_id' => 'TRACK123',
                'merchant_order_id' => 'ORDER123',
                'status' => 'Delivered',
            ],
        ];
        
        $shipment = $this->mapper->mapApiToShipment($apiData);
        
        $this->assertEquals('TRACK123', $shipment->trackingId);
        $this->assertEquals('pathao', $shipment->courierName);
        $this->assertEquals('ORDER123', $shipment->externalOrderId);
    }
    
    /**
     * Test mapping API response to Tracking.
     */
    public function testMapApiToTracking(): void
    {
        $apiData = [
            'consignment_id' => 'TRACK123',
            'status' => 'Delivered',
            'current_location' => 'Dhaka',
            'delivered_at' => '2024-01-15 10:30:00',
        ];
        
        $tracking = $this->mapper->mapApiToTracking($apiData);
        
        $this->assertEquals('TRACK123', $tracking->trackingId);
        $this->assertEquals('pathao', $tracking->courierName);
        $this->assertEquals(StatusMapper::DELIVERED, $tracking->status);
        $this->assertEquals('Dhaka', $tracking->currentLocation);
        $this->assertInstanceOf(\DateTimeInterface::class, $tracking->deliveredAt);
    }
    
    /**
     * Test mapping Rate to API format.
     */
    public function testMapRateToApi(): void
    {
        $rate = new Rate();
        $rate->fromCity = '1';
        $rate->toCity = '2';
        $rate->weight = 2.0;
        $rate->codAmount = 2000;
        $rate->serviceType = 'standard';
        $rate->courierData = ['store_id' => 12345];
        
        $apiData = $this->mapper->mapRateToApi($rate);
        
        $this->assertEquals(12345, $apiData['store_id']);
        $this->assertEquals(48, $apiData['delivery_type']);
        $this->assertEquals('2', $apiData['recipient_city']);
        $this->assertEquals(2000, $apiData['amount_collection']);
    }
    
    /**
     * Test mapping API response to Cod.
     */
    public function testMapApiToCod(): void
    {
        $apiData = [
            'consignment_id' => 'TRACK123', // Pathao uses consignment_id
            'cod_amount' => 1500,
            'cod_collected' => 1500,
            'is_settled' => true,
            'settled_at' => '2024-01-15 10:30:00',
        ];
        
        $cod = $this->mapper->mapApiToCod($apiData);
        
        $this->assertEquals('TRACK123', $cod->trackingId);
        $this->assertEquals('pathao', $cod->courierName);
        $this->assertEquals(1500, $cod->codAmount);
        $this->assertEquals(1500, $cod->codCollected);
        $this->assertEquals(0, $cod->codPending);
        $this->assertTrue($cod->isSettled);
    }
}
