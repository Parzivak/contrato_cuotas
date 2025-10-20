<?php
namespace App\Service\PaymentStrategy;

interface PaymentStrategyInterface
{
    /**
     * Calcula los detalles de una cuota individual.
     * @param float $baseAmount Valor base de la cuota (valor total / meses)
     * @return array{amount: float, interest: float, fee: float, total: float}
     */
    public function calculateInstallment(float $baseAmount): array;

    /**
     * Identificador único para esta estrategia (ej. 'paypal').
     */
    public function getIdentifier(): string;
}
