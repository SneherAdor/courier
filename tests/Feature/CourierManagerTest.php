<?php

namespace Millat\DeshCourier\Tests\Feature;

use Millat\DeshCourier\Core\CourierManager;
use Millat\DeshCourier\Core\CourierRegistry;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\Tests\TestCase;

class CourierManagerTest extends TestCase
{
    private CourierManager $manager;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new CourierManager();
    }
    
    /**
     * Test registering a courier.
     */
    public function testRegisterCourier(): void
    {
        $courier = $this->createMockShipmentCourier('test-courier');
        
        $this->manager->register($courier);
        
        $this->assertTrue($this->manager->getRegistry()->has('test-courier'));
    }
    
    /**
     * Test creating a shipment.
     */
    public function testCreateShipment(): void
    {
        $courier = $this->createMockShipmentCourier('test-courier');
        $this->manager->register($courier);
        
        $shipment = new Shipment();
        $shipment->recipientName = 'John Doe';
        $shipment->recipientPhone = '01712345678';
        $shipment->recipientAddress = 'House 123';
        $shipment->recipientCity = 'Dhaka';
        $shipment->senderName = 'Store';
        $shipment->senderPhone = '01787654321';
        $shipment->senderAddress = 'Shop 456';
        $shipment->senderCity = 'Dhaka';
        
        $result = $this->manager->createShipment('test-courier', $shipment);
        
        $this->assertInstanceOf(Shipment::class, $result);
        $this->assertEquals('TRACK123', $result->trackingId);
    }
    
    /**
     * Test creating shipment with unsupported courier.
     */
    public function testCreateShipmentWithUnsupportedCourier(): void
    {
        $courier = $this->createMock(CourierInterface::class);
        $courier->method('getName')->willReturn('no-shipment');
        $courier->method('getDisplayName')->willReturn('No Shipment');
        $courier->method('capabilities')->willReturn([]);
        $courier->method('supports')->willReturn(false);
        $courier->method('testConnection')->willReturn(true);
        
        $this->manager->register($courier);
        
        $shipment = new Shipment();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Courier 'no-shipment' does not support shipment creation.");
        
        $this->manager->createShipment('no-shipment', $shipment);
    }
    
    /**
     * Test finding couriers by capabilities.
     */
    public function testFindCouriersByCapabilities(): void
    {
        $courier1 = $this->createMockShipmentCourier('courier1');
        $courier2 = $this->createMock(CourierInterface::class);
        $courier2->method('getName')->willReturn('courier2');
        $courier2->method('getDisplayName')->willReturn('Courier 2');
        $courier2->method('capabilities')->willReturn(['tracking.realtime']);
        $courier2->method('supports')->willReturnCallback(fn($c) => $c === 'tracking.realtime');
        $courier2->method('testConnection')->willReturn(true);
        
        $this->manager->register($courier1);
        $this->manager->register($courier2);
        
        $couriers = $this->manager->findCouriersByCapabilities(['shipment.create']);
        
        $this->assertCount(1, $couriers);
        $this->assertArrayHasKey('courier1', $couriers);
    }
    
    /**
     * Create a mock courier that supports shipments.
     */
    private function createMockShipmentCourier(string $name): CourierInterface
    {
        // Create a concrete class that implements both interfaces
        $courier = new class($name) implements CourierInterface, ShipmentInterface {
            private string $name;
            
            public function __construct(string $name) {
                $this->name = $name;
            }
            
            public function getName(): string { return $this->name; }
            public function getDisplayName(): string { return ucfirst($this->name); }
            public function capabilities(): array { return ['shipment.create']; }
            public function supports(string $capability): bool { return $capability === 'shipment.create'; }
            public function testConnection(): bool { return true; }
            
            public function createShipment(Shipment|array $shipment): Shipment {
                if (is_array($shipment)) {
                    $shipment = Shipment::fromArray($shipment);
                }
                $shipment->trackingId = 'TRACK123';
                return $shipment;
            }
            public function updateShipment(string $trackingId, Shipment|array $shipment): Shipment {
                if (is_array($shipment)) {
                    $shipment = Shipment::fromArray($shipment);
                }
                return $shipment;
            }
            public function cancelShipment(string $trackingId, ?string $reason = null): bool { return true; }
            public function createBulkShipments(array $shipments): array { return $shipments; }
            public function generateLabel(string $trackingId, string $format = 'pdf'): string { return ''; }
            public function requestPickup(string $trackingId, array $pickupDetails = []): bool { return true; }
        };
        
        return $courier;
    }
}
