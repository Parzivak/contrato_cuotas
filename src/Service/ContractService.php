<?php

namespace App\Service;

use App\DTO\ContractRequest;
use App\DTO\InstallmentDetailResponse;
use App\DTO\ProjectionResponse;
use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\ValueObject\Money; // Importar
use App\ValueObject\PaymentMethod; // Importar

class ContractService
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly PaymentStrategyFactory $strategyFactory,
    ) {
    }

    public function createContract(ContractRequest $request): Contract
    {
        $contract = new Contract();
        $contract->setContractNumber($request->contractNumber);
        $contract->setContractDate($request->contractDate);
        // Instanciar Value Objects desde el DTO
        $contract->setTotalValue(new Money($request->totalValue));
        $contract->setPaymentMethod(new PaymentMethod($request->paymentMethod));

        $this->contractRepository->save($contract, true);

        return $contract;
    }

    public function projectInstallments(int $contractId, int $numberOfMonths): ?ProjectionResponse
    {
        $contract = $this->contractRepository->find($contractId);
        if (!$contract || !$contract->getTotalValue() || !$contract->getPaymentMethod()) {
            return null;
        }

        if ($numberOfMonths <= 0) {
            throw new \InvalidArgumentException('El número de meses debe ser positivo.');
        }

        // Obtener valores primitivos de los Value Objects
        $paymentMethodValue = $contract->getPaymentMethodValue();
        $totalValueAmount = $contract->getTotalValueAmount();

        $strategy = $this->strategyFactory->getStrategy($paymentMethodValue);
        $baseInstallmentAmount = $totalValueAmount / $numberOfMonths;

        $response = new ProjectionResponse();
        $response->totalContractValue = $totalValueAmount;

        for ($month = 1; $month <= $numberOfMonths; ++$month) {
            $calculation = $strategy->calculateInstallment($baseInstallmentAmount);
            $dueDate = $contract->getContractDate()->add(new \DateInterval("P{$month}M"));

            $response->installments[] = new InstallmentDetailResponse(
                monthNumber: $month,
                dueDate: $dueDate->format('Y-m-d'),
                baseAmount: $calculation['amount'],
                interest: $calculation['interest'],
                fee: $calculation['fee'],
                totalAmount: $calculation['total']
            );

            $response->totalInterestPaid += $calculation['interest'];
            $response->totalFeesPaid += $calculation['fee'];
        }

        $response->totalPaidWithFeesAndInterest = round(
            $response->totalContractValue + $response->totalInterestPaid + $response->totalFeesPaid,
            2
        );

        return $response;
    }
}
