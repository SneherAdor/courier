<?php

namespace Millat\DeshCourier\Tests\Unit\Exceptions;

use Millat\DeshCourier\Exceptions\ApiException;
use Millat\DeshCourier\Tests\TestCase;

class ApiExceptionTest extends TestCase
{
    /**
     * Test exception creation with all parameters.
     */
    public function testExceptionCreation(): void
    {
        $message = 'API Error';
        $code = 400;
        $courierName = 'pathao';
        $statusCode = 404;
        $apiResponse = ['error' => 'Not found'];
        
        $exception = new ApiException($message, $code, null, $courierName, $statusCode, $apiResponse);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($courierName, $exception->getCourierName());
        $this->assertEquals($statusCode, $exception->getStatusCode());
        $this->assertEquals($apiResponse, $exception->getApiResponse());
    }
    
    /**
     * Test exception with minimal parameters.
     */
    public function testExceptionWithMinimalParameters(): void
    {
        $exception = new ApiException('Error');
        
        $this->assertEquals('Error', $exception->getMessage());
        $this->assertNull($exception->getCourierName());
        $this->assertNull($exception->getStatusCode());
        $this->assertNull($exception->getApiResponse());
    }
}
