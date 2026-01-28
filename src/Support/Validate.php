<?php

namespace Millat\DeshCourier\Support;

trait Validate
{
    protected function validate($data, $rules, $messages = [], $descriptions = []): void
    {
        // Check if $data is a DTO (any object), if so, convert to array
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                $data = (array) $data;
            }
        }
        
        // Retrieve courier name for better error context
        $courierName = $this->getCourierName();
        
        // Create validator with courier name for enhanced error messages
        $validator = new Validator(
            $data,
            $rules,
            $messages,
            $descriptions,
            $courierName
        );
        
        // Validate data against rules (throws ValidationException if validation fails)
        $validator->validate();
    }
    
    /**
     * Get the courier name for error reporting.
     * 
     * @return string
     */
    private function getCourierName(): string
    {
        return method_exists($this, 'getName') ? $this->getName() : 'unknown';
    }
}
