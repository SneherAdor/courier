<?php

namespace Millat\DeshCourier\Drivers\Pathao\Handlers;

use Millat\DeshCourier\DTO\Rate;
use Millat\DeshCourier\Support\DtoNormalizer;
use Millat\DeshCourier\Support\Validate;
use Millat\DeshCourier\Exceptions\ApiException;
use Millat\DeshCourier\Exceptions\ValidationException;

class RateHandler extends PathaoHandler
{
    public function estimate(Rate|array $rateRequest): Rate
    {
        $rateRequest = DtoNormalizer::rate($rateRequest);
        
        $this->validateRateRequest($rateRequest);
        
        $response = $this->post('aladdin/api/v1/merchant/price-plan', [
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
                'storeId' => 'required|integer',
                'weight' => 'required|float|min:0.5|max:10',
                'toCity' => 'required|integer',
                'toZone' => 'required|integer',
                'deliveryType' => 'required|integer',
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
