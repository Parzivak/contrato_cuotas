<?php

namespace App\Service\PaymentStrategy;

class PayPalStrategy implements PaymentStrategyInterface
{
    private const INTEREST_RATE = 0.01;
    private const FEE_RATE = 0.02;

    public function calculateInstallment(float $baseAmount): array
    {
        $interest = $baseAmount * self::INTEREST_RATE;
        $fee = $baseAmount * self::FEE_RATE;
        $total = $baseAmount + $interest + $fee;

        return [
            'amount' => round($baseAmount, 2),
            'interest' => round($interest, 2),
            'fee' => round($fee, 2),
            'total' => round($total, 2),
        ];
    }

    public function getIdentifier(): string
    {
        return 'paypal';
    }
}
