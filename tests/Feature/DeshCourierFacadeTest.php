<?php

namespace Millat\DeshCourier\Tests\Feature;

use Millat\DeshCourier\DeshCourier;
use Millat\DeshCourier\Contracts\CourierInterface;
use Millat\DeshCourier\Contracts\ShipmentInterface;
use Millat\DeshCourier\DTO\Shipment;
use Millat\DeshCourier\Tests\TestCase;

class DeshCourierFacadeTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        // Reset facade state
        $reflection = new \ReflectionClass(DeshCourier::class);
        $managerProperty = $reflection->getProperty('manager');
        $managerProperty->setAccessible(true);
        $managerProperty->setValue(null, null);
    }
    
    /**
     * Test registering a courier via facade.
     */
    public function testRegisterCourier(): void
    {
        $courier = $this->createMockCourier('test-courier');
        
        DeshCourier::register($courier);
        
        $this->assertTrue(in_array('test-courier', DeshCourier::available()));
    }
    
    /**
     * Test getting a courier via facade.
     */
    public function testUseCourier(): void
    {
        $courier = $this->createMockCourier('test-courier');
        DeshCourier::register($courier);
        
        $retrieved = DeshCourier::use('test-courier');
        
        $this->assertEquals($courier, $retrieved);
    }
    
    /**
     * Test creating shipment via facade.
     */
    public function testCreateShipment(): void
    {
        $courier = $this->createMockShipmentCourier('test-courier');
        DeshCourier::register($courier);
        
        $shipment = new Shipment();
        $shipment->recipientName = 'John Doe';
        $shipment->recipientPhone = '01712345678';
        $shipment->recipientAddress = 'House 123';
        $shipment->recipientCity = 'Dhaka';
        $shipment->senderName = 'Store';
        $shipment->senderPhone = '01787654321';
        $shipment->senderAddress = 'Shop 456';
        $shipment->senderCity = 'Dhaka';
        
        $result = DeshCourier::createShipment('test-courier', $shipment);
        
        $this->assertInstanceOf(Shipment::class, $result);
        $this->assertEquals('TRACK123', $result->trackingId);
    }
    
    /**
     * Create a mock courier.
     */
    private function createMockCourier(string $name): CourierInterface
    {
        $courier = $this->createMock(CourierInterface::class);
        $courier->method('getName')->willReturn($name);
        $courier->method('getDisplayName')->willReturn(ucfirst($name));
        $courier->method('capabilities')->willReturn([]);
        $courier->method('supports')->willReturn(false);
        $courier->method('testConnection')->willReturn(true);
        
        return $courier;
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
