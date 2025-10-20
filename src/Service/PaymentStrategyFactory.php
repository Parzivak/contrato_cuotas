<?php
namespace App\Service;

use App\Service\PaymentStrategy\PaymentStrategyInterface;

class PaymentStrategyFactory
{
    /** @var PaymentStrategyInterface[] */
    private array $strategies = [];

    // Inyección automática de todas las estrategias gracias al tagging
    public function __construct(iterable $strategies)
    {
        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->getIdentifier()] = $strategy;
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
