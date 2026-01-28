<?php

namespace Millat\DeshCourier\Exceptions;

class ApiException extends CourierException
{
    protected ?int $statusCode = null;
    protected ?array $apiResponse = null;
    
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
        ?string $courierName = null,
        ?int $statusCode = null,
        ?array $apiResponse = null
    ) {
        parent::__construct($message, $code, $previous, $courierName);
        $this->statusCode = $statusCode;
        $this->apiResponse = $apiResponse;
    }
    
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
    
    public function getApiResponse(): ?array
    {
        return $this->apiResponse;
    }
}
