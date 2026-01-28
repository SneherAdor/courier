<?php

namespace Millat\DeshCourier\Exceptions;

/**
 * Thrown when validation fails.
 * 
 * Similar to Laravel's ValidationException, contains field-specific errors.
 */
class ValidationException extends CourierException
{
    protected array $errors = [];
    protected array $fieldDescriptions = [];
    
    /**
     * @param array<string, array<string>> $errors Field name => array of error messages
     * @param array<string, string> $fieldDescriptions Field name => description
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string|null $courierName
     */
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
    
    /**
     * Get all validation errors.
     * 
     * @return array<string, array<string>> Field name => array of error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get errors for a specific field.
     * 
     * @param string $field
     * @return array<string>
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Get field descriptions.
     * 
     * @return array<string, string> Field name => description
     */
    public function getFieldDescriptions(): array
    {
        return $this->fieldDescriptions;
    }
    
    /**
     * Get description for a specific field.
     * 
     * @param string $field
     * @return string|null
     */
    public function getFieldDescription(string $field): ?string
    {
        return $this->fieldDescriptions[$field] ?? null;
    }
    
    /**
     * Check if a specific field has errors.
     * 
     * @param string $field
     * @return bool
     */
    public function hasFieldError(string $field): bool
    {
        return !empty($this->errors[$field]);
    }
    
    /**
     * Get all error messages as a flat array.
     * 
     * @return array<string>
     */
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
    
    /**
     * Get formatted error message.
     * 
     * @return string
     */
    public function getFormattedMessage(): string
    {
        $messages = $this->getAllMessages();
        return implode("\n", $messages);
    }
}
