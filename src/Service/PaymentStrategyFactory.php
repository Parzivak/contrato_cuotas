<?php

namespace App\Service;

use App\Service\PaymentStrategy\PaymentStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class PaymentStrategyFactory
{
    /** @var PaymentStrategyInterface[] */
    private array $strategies = [];

    public function __construct(
        #[TaggedIterator('app.payment_strategy')] iterable $strategies,
    ) {
        foreach ($strategies as $strategy) {
            if ($strategy instanceof PaymentStrategyInterface) {
                $this->strategies[$strategy->getIdentifier()] = $strategy;
            }
        }
    }

    public function getStrategy(string $identifier): PaymentStrategyInterface
    {
        if (!isset($this->strategies[$identifier])) {
            throw new \InvalidArgumentException("Estrategia de pago desconocida: {$identifier}");
        }

        return $this->strategies[$identifier];
    }
}
