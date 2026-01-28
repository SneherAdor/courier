<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\Support\DtoNormalizer;
use Millat\DeshCourier\Support\Validate;
use Millat\DeshCourier\Exceptions\ApiException;

class RateHandler extends PathaoHandler
{
    public function estimate(Rate|array $rateRequest): Rate
    {
        $rateRequest = DtoNormalizer::rate($rateRequest);
        
        $this->validateRateRequest($rateRequest);
        
        $response = $this->post('aladdin/api/v1/rates', [
            'json' => $this->getMapper()->mapRateToApi($rateRequest),
        ]);
        
        $this->handleResponse($response, [200], 'Failed to estimate rate');
        
        return $this->getMapper()->mapApiToRate($response['data'], $rateRequest);
    }
    
    public function getServiceTypes(): array
    {
        return [
            'same_day' => [
                'name' => 'Same Day Delivery',
                'sla' => 'Same day',
                'available' => true,
            ],
            'next_day' => [
                'name' => 'Next Day Delivery',
                'sla' => 'Next day',
                'available' => true,
            ],
            'standard' => [
                'name' => 'Standard Delivery',
                'sla' => '2-3 days',
                'available' => true,
            ],
        ];
    }
    
    protected function validateRateRequest(Rate $rateRequest): void
    {
        $this->validate(
            $rateRequest,
            [
                'weight' => 'required|numeric|min:0.1|max:50',
                'millat' => 'required|numeric|min:0',
            ],
            [
                'weight.required' => 'Weight is required.',
                'weight.numeric' => 'Weight must be a number.',
            ],
            [
                'weight' => 'Package weight in kilograms (between 0.1 and 50 kg). If unsure, use 1 kg.',
                'millat' => 'Millat is required and must be a number. If unsure, use 0.',
            ]
        );
    }
    
    protected function handleResponse(array $response, array $successStatuses, string $defaultMessage): void
    {
        if (!in_array($response['status'], $successStatuses)) {
            throw new ApiException(
                $response['data']['message'] ?? $defaultMessage,
                $response['status'],
                null,
                'pathao',
                $response['status'],
                $response['data'] ?? []
            );
        }
    }
}
