<?php

namespace Millat\DeshCourier\Support;

use Millat\DeshCourier\Exceptions\ValidationException;

/**
 * Laravel-style validator for courier parameters.
 * 
 * Supports common validation rules:
 * - required
 * - string
 * - numeric
 * - integer
 * - float
 * - email
 * - phone
 * - min:value
 * - max:value
 * - in:value1,value2,...
 * - regex:pattern
 * - array
 * - boolean
 */
class Validator
{
    protected array $data;
    protected array $rules;
    protected array $messages;
    protected array $descriptions;
    protected array $errors = [];
    protected ?string $courierName = null;
    
    /**
     * Flag to track if exception handler has been registered.
     * 
     * @var bool
     */
    private static bool $exceptionHandlerRegistered = false;
    
    /**
     * Create a new validator instance.
     * 
     * @param array<string, mixed> $data Data to validate
     * @param array<string, string|array> $rules Validation rules (field => rules)
     * @param array<string, string> $messages Custom error messages (field.rule => message)
     * @param array<string, string> $descriptions Field descriptions (field => description)
     * @param string|null $courierName Optional courier name for error messages
     */
    public function __construct(
        array $data,
        array $rules,
        array $messages = [],
        array $descriptions = [],
        ?string $courierName = null
    ) {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
        $this->descriptions = $descriptions;
        $this->courierName = $courierName;
        
        // Automatically register exception handler for browser display (only once)
        self::registerExceptionHandler();
    }
    
    /**
     * Register exception handler for browser and CLI display of ValidationException.
     * Only registers once, even if called multiple times.
     * 
     * @return void
     */
    public static function registerExceptionHandler(): void
    {
        // Only register once
        if (self::$exceptionHandlerRegistered) {
            return;
        }
        
        $isCli = self::isCliContext();
        
        set_exception_handler(function ($exception) use ($isCli) {
            // Handle ValidationException specially
            if ($exception instanceof ValidationException) {
                $message = $exception->getMessage();
                
                if ($isCli) {
                    // CLI: Output formatted message and exit cleanly (no stack trace)
                    echo $message . "\n";
                    exit(1);
                } else {
                    // Browser: Check if message contains complete HTML document
                    if (strpos($message, '<!DOCTYPE html') === 0 || strpos($message, '<html') === 0) {
                        // ValidationException message already contains complete HTML document
                        http_response_code(400);
                        echo $message;
                        exit(1);
                    }
                }
            }
            
            // For other exceptions, use default handler
            // This allows the default PHP error handler to work normally
            restore_exception_handler();
            throw $exception;
        });
        
        self::$exceptionHandlerRegistered = true;
    }
    
    /**
     * Check if the current context is CLI (command line interface).
     * 
     * @return bool
     */
    private static function isCliContext(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg'], true);
    }
    
    /**
     * Validate the data.
     * 
     * @return bool
     * @throws ValidationException
     */
    public function validate(): bool
    {
        $this->errors = [];
        
        foreach ($this->rules as $field => $rules) {
            $this->validateField($field, $rules);
        }
        
        if (!empty($this->errors)) {
            $errorMessage = $this->buildErrorMessage();
            throw new ValidationException(
                $this->errors,
                $this->descriptions,
                $errorMessage,
                0,
                null,
                $this->courierName
            );
        }
        
        return true;
    }
    
    /**
     * Validate a single field.
     * 
     * @param string $field
     * @param string|array $rules
     */
    protected function validateField(string $field, string|array $rules): void
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        $value = $this->data[$field] ?? null;
        
        // Check if 'required' rule exists
        $isRequired = false;
        $ruleNames = [];
        foreach ($rules as $rule) {
            $rule = trim($rule);
            if (empty($rule)) {
                continue;
            }
            $ruleParts = explode(':', $rule, 2);
            $ruleName = $ruleParts[0];
            $ruleNames[] = $ruleName;
            if ($ruleName === 'required') {
                $isRequired = true;
            }
        }
        
        // Skip validation for optional fields that are empty (except 'required' rule itself)
        if (!$isRequired && ($value === null || $value === '')) {
            return;
        }
        
        foreach ($rules as $rule) {
            $rule = trim($rule);
            
            if (empty($rule)) {
                continue;
            }
            
            // Handle rules with parameters (e.g., min:5, max:10)
            $ruleParts = explode(':', $rule, 2);
            $ruleName = $ruleParts[0];
            $ruleValue = $ruleParts[1] ?? null;
            
            $this->applyRule($field, $ruleName, $value, $ruleValue);
        }
    }
    
    /**
     * Apply a validation rule.
     * 
     * @param string $field
     * @param string $ruleName
     * @param mixed $value
     * @param string|null $ruleValue
     */
    protected function applyRule(string $field, string $ruleName, mixed $value, ?string $ruleValue): void
    {
        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, $ruleName, "The {$field} field is required.");
                }
                break;
                
            case 'string':
                if ($value !== null && !is_string($value)) {
                    $this->addError($field, $ruleName, "The {$field} must be a string.");
                }
                break;
                
            case 'numeric':
                if ($value !== null && !is_numeric($value)) {
                    $this->addError($field, $ruleName, "The {$field} must be a number.");
                }
                break;
                
            case 'integer':
                if ($value !== null && !is_int($value) && !(is_string($value) && ctype_digit($value))) {
                    $this->addError($field, $ruleName, "The {$field} must be an integer.");
                }
                break;
                
            case 'float':
                if ($value !== null && !is_float($value) && !is_numeric($value)) {
                    $this->addError($field, $ruleName, "The {$field} must be a float.");
                }
                break;
                
            case 'email':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $ruleName, "The {$field} must be a valid email address.");
                }
                break;
                
            case 'phone':
                if ($value !== null && !preg_match('/^[\d\s\-\+\(\)]+$/', $value)) {
                    $this->addError($field, $ruleName, "The {$field} must be a valid phone number.");
                }
                break;
                
            case 'min':
                if ($ruleValue !== null && $value !== null) {
                    $min = is_numeric($ruleValue) ? (float)$ruleValue : strlen($ruleValue);
                    $compareValue = is_numeric($value) ? (float)$value : strlen((string)$value);
                    if ($compareValue < $min) {
                        $this->addError($field, $ruleName, "The {$field} must be at least {$ruleValue}.");
                    }
                }
                break;
                
            case 'max':
                if ($ruleValue !== null && $value !== null) {
                    $max = is_numeric($ruleValue) ? (float)$ruleValue : strlen($ruleValue);
                    $compareValue = is_numeric($value) ? (float)$value : strlen((string)$value);
                    if ($compareValue > $max) {
                        $this->addError($field, $ruleName, "The {$field} may not be greater than {$ruleValue}.");
                    }
                }
                break;
                
            case 'in':
                if ($ruleValue !== null && $value !== null) {
                    $allowed = array_map('trim', explode(',', $ruleValue));
                    if (!in_array($value, $allowed)) {
                        $this->addError($field, $ruleName, "The {$field} must be one of: " . implode(', ', $allowed) . ".");
                    }
                }
                break;
                
            case 'regex':
                if ($ruleValue !== null && $value !== null && !preg_match($ruleValue, (string)$value)) {
                    $this->addError($field, $ruleName, "The {$field} format is invalid.");
                }
                break;
                
            case 'array':
                if ($value !== null && !is_array($value)) {
                    $this->addError($field, $ruleName, "The {$field} must be an array.");
                }
                break;
                
            case 'boolean':
                if ($value !== null && !is_bool($value) && $value !== 0 && $value !== 1 && $value !== '0' && $value !== '1') {
                    $this->addError($field, $ruleName, "The {$field} must be a boolean.");
                }
                break;
        }
    }
    
    /**
     * Add an error for a field.
     * 
     * @param string $field
     * @param string $rule
     * @param string $defaultMessage
     */
    protected function addError(string $field, string $rule, string $defaultMessage): void
    {
        $key = "{$field}.{$rule}";
        $message = $this->messages[$key] ?? $defaultMessage;
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get validation errors.
     * 
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Check if validation has errors.
     * 
     * @return bool
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Check if validation passes.
     * 
     * @return bool
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }
    
    /**
     * Build a user-friendly error message from validation errors.
     * Formats message appropriately for CLI, browser, or API contexts.
     * 
     * @return string
     */
    private function buildErrorMessage(): string
    {
        $errorCount = count($this->errors);
        $courierName = $this->courierName ?? 'unknown';
        $isCli = self::isCliContext();
        
        // Build list of missing/invalid fields with descriptions
        $fieldList = [];
        foreach ($this->errors as $field => $fieldErrors) {
            $fieldLabel = $this->descriptions[$field] ?? $field;
            $errorText = implode(', ', $fieldErrors);
            
            // Check if description exists and is different from field name (more descriptive)
            $hasDescription = isset($this->descriptions[$field]) && 
                             $this->descriptions[$field] !== $field &&
                             strlen($this->descriptions[$field]) > strlen($field);
            
            if ($isCli) {
                // CLI: Use ANSI color codes for terminal
                if ($hasDescription) {
                    $fieldList[] = sprintf(
                        "\033[31m  ⚠  \033[1m%s\033[0m\033[31m: %s\033[0m\n     \033[36mℹ  %s\033[0m",
                        $fieldLabel,
                        $errorText,
                        $this->descriptions[$field]
                    );
                } else {
                    $fieldList[] = sprintf(
                        "\033[31m  ⚠  \033[1m%s\033[0m\033[31m: %s\033[0m",
                        $fieldLabel,
                        $errorText
                    );
                }
            } else {
                // Browser: Use HTML with professional styling
                if ($hasDescription) {
                    $fieldList[] = sprintf(
                        '<div style="margin: 8px 0; padding: 12px; background-color: #FFF3E0; border-left: 4px solid #FF9800; border-radius: 4px;">' .
                        '<div style="margin-bottom: 6px;">' .
                        '<span style="color: #E65100; font-weight: 600; font-size: 14px;">⚠</span> ' .
                        '<strong style="color: #D32F2F;">%s</strong>: ' .
                        '<span style="color: #424242;">%s</span>' .
                        '</div>' .
                        '<div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #FFE0B2; color: #616161; font-size: 13px;">' .
                        '<span style="color: #1976D2;">ℹ</span> <em>%s</em>' .
                        '</div>' .
                        '</div>',
                        htmlspecialchars($fieldLabel, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($errorText, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($this->descriptions[$field], ENT_QUOTES, 'UTF-8')
                    );
                } else {
                    $fieldList[] = sprintf(
                        '<div style="margin: 8px 0; padding: 10px; background-color: #FFF3E0; border-left: 4px solid #FF9800; border-radius: 4px;">' .
                        '<span style="color: #E65100; font-weight: 600; font-size: 14px;">⚠</span> ' .
                        '<strong style="color: #D32F2F;">%s</strong>: ' .
                        '<span style="color: #424242;">%s</span>' .
                        '</div>',
                        htmlspecialchars($fieldLabel, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($errorText, ENT_QUOTES, 'UTF-8')
                    );
                }
            }
        }
        
        // Create eye-catching, professional error message with visual emphasis
        if ($isCli) {
            // CLI formatting with ANSI colors
            $separator = "\033[1;33m" . str_repeat('═', 60) . "\033[0m";
            $header = sprintf(
                "\033[1;33m⚠️  MISSING REQUIRED INFORMATION FOR %s COURIER\033[0m",
                strtoupper($courierName)
            );
            
            $message = sprintf(
                "\n%s\n%s\n%s\n\n\033[1mPlease provide the following required field(s):\033[0m\n\n%s\n",
                $separator,
                $header,
                $separator,
                implode("\n", $fieldList)
            );
        } else {
            // Browser formatting with complete HTML document
            $content = sprintf(
                '<div style="font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; ' .
                'max-width: 800px; margin: 20px auto; padding: 0; background: #fff;">' .
                '<div style="background: linear-gradient(135deg, #FF6B35 0%%, #F7931E 100%%); ' .
                'color: white; padding: 20px 25px; border-radius: 8px 8px 0 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' .
                '<div style="font-size: 18px; font-weight: 700; margin-bottom: 5px;">⚠️ Missing Required Information</div>' .
                '<div style="font-size: 13px; opacity: 0.95;">%s Courier</div>' .
                '</div>' .
                '<div style="background: #FAFAFA; padding: 20px 25px; border: 1px solid #E0E0E0; ' .
                'border-top: none; border-radius: 0 0 8px 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' .
                '<div style="color: #424242; font-size: 15px; font-weight: 600; margin-bottom: 15px; ' .
                'padding-bottom: 10px; border-bottom: 2px solid #FF9800;">' .
                'Please provide the following required field(s):' .
                '</div>' .
                '%s' .
                '</div>' .
                '</div>',
                htmlspecialchars(strtoupper($courierName), ENT_QUOTES, 'UTF-8'),
                implode('', $fieldList)
            );
            
            // Wrap in complete HTML document for direct browser output
            $message = sprintf(
                '<!DOCTYPE html>' .
                '<html lang="en">' .
                '<head>' .
                '<meta charset="UTF-8">' .
                '<meta name="viewport" content="width=device-width, initial-scale=1.0">' .
                '<title>Validation Error - %s Courier</title>' .
                '<style>' .
                'body { margin: 0; padding: 20px; background: #f5f5f5; }' .
                '</style>' .
                '</head>' .
                '<body>%s</body>' .
                '</html>',
                htmlspecialchars(strtoupper($courierName), ENT_QUOTES, 'UTF-8'),
                $content
            );
        }
        
        return $message;
    }
    
    /**
     * Build a user-friendly description/help message.
     * Formats message appropriately for CLI, browser, or API contexts.
     * 
     * @param string $title The title/header of the description
     * @param array<string, string> $items Array of field => description pairs
     * @param string|null $subtitle Optional subtitle or additional context
     * @return string
     */
    public function buildDescriptionMessage(string $title, array $items, ?string $subtitle = null): string
    {
        $courierName = $this->courierName ?? 'unknown';
        $isCli = $this->isCliContext();
        
        // Build list of fields with descriptions
        $itemList = [];
        foreach ($items as $field => $description) {
            $fieldLabel = $this->descriptions[$field] ?? $field;
            
            if ($isCli) {
                // CLI: Use ANSI color codes for terminal (blue/cyan theme)
                $itemList[] = sprintf(
                    "\033[36m  ℹ  \033[1m%s\033[0m\033[36m: %s\033[0m",
                    $fieldLabel,
                    $description
                );
            } else {
                // Browser: Use HTML with professional styling (blue/info theme)
                $itemList[] = sprintf(
                    '<div style="margin: 8px 0; padding: 12px; background-color: #E3F2FD; border-left: 4px solid #2196F3; border-radius: 4px; transition: background-color 0.2s;">' .
                    '<span style="color: #1976D2; font-weight: 600; font-size: 14px;">ℹ</span> ' .
                    '<strong style="color: #1565C0;">%s</strong>: ' .
                    '<span style="color: #424242; line-height: 1.5;">%s</span>' .
                    '</div>',
                    htmlspecialchars($fieldLabel, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($description, ENT_QUOTES, 'UTF-8')
                );
            }
        }
        
        // Create eye-catching, professional description message
        if ($isCli) {
            // CLI formatting with ANSI colors (blue/cyan theme)
            $separator = "\033[1;36m" . str_repeat('═', 70) . "\033[0m";
            $header = sprintf(
                "\033[1;36mℹ️  %s\033[0m",
                strtoupper($title)
            );
            
            $message = sprintf(
                "\n%s\n%s\n%s\n",
                $separator,
                $header,
                $separator
            );
            
            if ($subtitle) {
                $message .= "\n\033[1m" . $subtitle . "\033[0m\n\n";
            } else {
                $message .= "\n";
            }
            
            $message .= implode("\n", $itemList) . "\n";
        } else {
            // Browser formatting with HTML (blue/info theme)
            $subtitleHtml = $subtitle ? sprintf(
                '<div style="color: #424242; font-size: 14px; margin-bottom: 15px; padding: 10px; ' .
                'background-color: #F5F5F5; border-radius: 4px; line-height: 1.6;">%s</div>',
                htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8')
            ) : '';
            
            $content = sprintf(
                '<div style="font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; ' .
                'max-width: 800px; margin: 20px auto; padding: 0; background: #fff;">' .
                '<div style="background: linear-gradient(135deg, #2196F3 0%%, #1976D2 100%%); ' .
                'color: white; padding: 20px 25px; border-radius: 8px 8px 0 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' .
                '<div style="font-size: 18px; font-weight: 700; margin-bottom: 5px;">ℹ️ %s</div>' .
                '<div style="font-size: 13px; opacity: 0.95;">%s Courier</div>' .
                '</div>' .
                '<div style="background: #FAFAFA; padding: 20px 25px; border: 1px solid #E0E0E0; ' .
                'border-top: none; border-radius: 0 0 8px 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' .
                '%s' .
                '<div style="color: #424242; font-size: 15px; font-weight: 600; margin-bottom: 15px; ' .
                'padding-bottom: 10px; border-bottom: 2px solid #2196F3;">' .
                'Field Information:' .
                '</div>' .
                '%s' .
                '</div>' .
                '</div>',
                htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(strtoupper($courierName), ENT_QUOTES, 'UTF-8'),
                $subtitleHtml,
                implode('', $itemList)
            );
            
            // Wrap in complete HTML document for direct browser output
            $message = sprintf(
                '<!DOCTYPE html>' .
                '<html lang="en">' .
                '<head>' .
                '<meta charset="UTF-8">' .
                '<meta name="viewport" content="width=device-width, initial-scale=1.0">' .
                '<title>%s - %s Courier</title>' .
                '<style>' .
                'body { margin: 0; padding: 20px; background: #f5f5f5; }' .
                '</style>' .
                '</head>' .
                '<body>%s</body>' .
                '</html>',
                htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(strtoupper($courierName), ENT_QUOTES, 'UTF-8'),
                $content
            );
        }
        
        return $message;
    }
    
}
