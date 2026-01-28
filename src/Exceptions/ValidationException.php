<?php

namespace Millat\DeshCourier\Exceptions;

class ValidationException extends CourierException
{
    protected array $errors = [];
    protected array $fieldDescriptions = [];
    
    public function __construct(
        array $errors = [],
        array $fieldDescriptions = [],
        string $message = "Validation failed",
        int $code = 0,
        ?\Throwable $previous = null,
        ?string $courierName = null
    ) {
        parent::__construct($message, $code, $previous, $courierName);
        $this->errors = $errors;
        $this->fieldDescriptions = $fieldDescriptions;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    public function getFieldDescriptions(): array
    {
        return $this->fieldDescriptions;
    }
    
    public function getFieldDescription(string $field): ?string
    {
        return $this->fieldDescriptions[$field] ?? null;
    }
    
    public function hasFieldError(string $field): bool
    {
        return !empty($this->errors[$field]);
    }
    
    public function getAllMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = $field . ': ' . $error;
            }
        }
        return $messages;
    }
    
    public function getFormattedMessage(): string
    {
        $messages = $this->getAllMessages();
        return implode("\n", $messages);
    }
}
