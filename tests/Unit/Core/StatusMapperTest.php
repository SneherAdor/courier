<?php

namespace Millat\DeshCourier\Tests\Unit\Core;

use Millat\DeshCourier\Core\StatusMapper;
use Millat\DeshCourier\Tests\TestCase;

class StatusMapperTest extends TestCase
{
    /**
     * Test that canonical statuses are defined.
     */
    public function testCanonicalStatusesAreDefined(): void
    {
        $statuses = StatusMapper::getCanonicalStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains(StatusMapper::CREATED, $statuses);
        $this->assertContains(StatusMapper::PICKED, $statuses);
        $this->assertContains(StatusMapper::IN_TRANSIT, $statuses);
        $this->assertContains(StatusMapper::OUT_FOR_DELIVERY, $statuses);
        $this->assertContains(StatusMapper::DELIVERED, $statuses);
        $this->assertContains(StatusMapper::FAILED, $statuses);
        $this->assertContains(StatusMapper::RETURNED, $statuses);
        $this->assertContains(StatusMapper::CANCELLED, $statuses);
    }
    
    /**
     * Test status mapping with custom mapping.
     */
    public function testMapWithCustomMapping(): void
    {
        $customMapping = [
            'Pending' => StatusMapper::CREATED,
            'Picked Up' => StatusMapper::PICKED,
            'Delivered' => StatusMapper::DELIVERED,
        ];
        
        $this->assertEquals(StatusMapper::CREATED, StatusMapper::map('Pending', $customMapping));
        $this->assertEquals(StatusMapper::PICKED, StatusMapper::map('Picked Up', $customMapping));
        $this->assertEquals(StatusMapper::DELIVERED, StatusMapper::map('Delivered', $customMapping));
    }
    
    /**
     * Test status mapping with default patterns.
     */
    public function testMapWithDefaultPatterns(): void
    {
        $this->assertEquals(StatusMapper::CREATED, StatusMapper::map('pending'));
        $this->assertEquals(StatusMapper::CREATED, StatusMapper::map('PENDING'));
        $this->assertEquals(StatusMapper::CREATED, StatusMapper::map('booked'));
        $this->assertEquals(StatusMapper::PICKED, StatusMapper::map('picked'));
        $this->assertEquals(StatusMapper::PICKED, StatusMapper::map('pickup'));
        $this->assertEquals(StatusMapper::IN_TRANSIT, StatusMapper::map('in transit'));
        $this->assertEquals(StatusMapper::IN_TRANSIT, StatusMapper::map('IN_TRANSIT'));
        $this->assertEquals(StatusMapper::OUT_FOR_DELIVERY, StatusMapper::map('out for delivery'));
        $this->assertEquals(StatusMapper::DELIVERED, StatusMapper::map('delivered'));
        $this->assertEquals(StatusMapper::DELIVERED, StatusMapper::map('DELIVERED'));
        $this->assertEquals(StatusMapper::FAILED, StatusMapper::map('failed'));
        $this->assertEquals(StatusMapper::RETURNED, StatusMapper::map('returned'));
        $this->assertEquals(StatusMapper::CANCELLED, StatusMapper::map('cancelled'));
    }
    
    /**
     * Test that unknown status defaults to CREATED.
     */
    public function testMapUnknownStatusDefaultsToCreated(): void
    {
        $result = StatusMapper::map('unknown_status_xyz');
        $this->assertEquals(StatusMapper::CREATED, $result);
    }
    
    /**
     * Test terminal status detection.
     */
    public function testIsTerminal(): void
    {
        $this->assertTrue(StatusMapper::isTerminal(StatusMapper::DELIVERED));
        $this->assertTrue(StatusMapper::isTerminal(StatusMapper::FAILED));
        $this->assertTrue(StatusMapper::isTerminal(StatusMapper::RETURNED));
        $this->assertTrue(StatusMapper::isTerminal(StatusMapper::CANCELLED));
        
        $this->assertFalse(StatusMapper::isTerminal(StatusMapper::CREATED));
        $this->assertFalse(StatusMapper::isTerminal(StatusMapper::PICKED));
        $this->assertFalse(StatusMapper::isTerminal(StatusMapper::IN_TRANSIT));
        $this->assertFalse(StatusMapper::isTerminal(StatusMapper::OUT_FOR_DELIVERY));
    }
    
    /**
     * Test display name retrieval.
     */
    public function testGetDisplayName(): void
    {
        $this->assertEquals('Created', StatusMapper::getDisplayName(StatusMapper::CREATED));
        $this->assertEquals('Picked Up', StatusMapper::getDisplayName(StatusMapper::PICKED));
        $this->assertEquals('In Transit', StatusMapper::getDisplayName(StatusMapper::IN_TRANSIT));
        $this->assertEquals('Out for Delivery', StatusMapper::getDisplayName(StatusMapper::OUT_FOR_DELIVERY));
        $this->assertEquals('Delivered', StatusMapper::getDisplayName(StatusMapper::DELIVERED));
        $this->assertEquals('Failed', StatusMapper::getDisplayName(StatusMapper::FAILED));
        $this->assertEquals('Returned', StatusMapper::getDisplayName(StatusMapper::RETURNED));
        $this->assertEquals('Cancelled', StatusMapper::getDisplayName(StatusMapper::CANCELLED));
    }
    
    /**
     * Test case-insensitive mapping.
     */
    public function testCaseInsensitiveMapping(): void
    {
        $this->assertEquals(StatusMapper::DELIVERED, StatusMapper::map('DELIVERED'));
        $this->assertEquals(StatusMapper::DELIVERED, StatusMapper::map('delivered'));
        $this->assertEquals(StatusMapper::DELIVERED, StatusMapper::map('Delivered'));
        $this->assertEquals(StatusMapper::DELIVERED, StatusMapper::map('DeLiVeReD'));
    }
}
