<?php

namespace Millat\DeshCourier\Exceptions;

/**
 * Base exception for all courier-related errors.
 */
class CourierException extends \Exception
{
    protected ?string $courierName = null;
    protected ?array $context = null;
    
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
        ?string $courierName = null,
        ?array $context = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->courierName = $courierName;
        $this->context = $context;
    }
    
    public function getCourierName(): ?string
    {
        return $this->courierName;
    }
    
    public function getContext(): ?array
    {
        return $this->context;
    }
}
