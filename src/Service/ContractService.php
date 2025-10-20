<?php
namespace App\Service\PaymenStrategy;

use App\DTO\ContractRequest;
use App\DTO\InstallmentDetailResponse;
use App\DTO\ProjectionResponse;
use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Service\PaymentStrategy;

// O EntityManagerInterface

class ContractService
{
    public function __construct(
        private readonly ContractRepository $contractRepository, // Inyecta el repositorio
        private readonly PaymentStrategyFactory $strategyFactory
    ) {}

    public function createContract(ContractRequest $request): Contract
    {
        $contract = new Contract();
        $contract->setContractNumber($request->contractNumber);
        $contract->setContractDate($request->contractDate);
        $contract->setTotalValue((string)$request->totalValue); // Doctrine maneja string para decimal
        $contract->setPaymentMethod($request->paymentMethod);

        $this->contractRepository->save($contract, true); // Usamos el repositorio

        return $contract;
    }

    public function projectInstallments(int $contractId, int $numberOfMonths): ?ProjectionResponse
    {
        $contract = $this->contractRepository->find($contractId);
        if (!$contract) {
            return null; // El controlador manejará el 404
        }

        if ($numberOfMonths <= 0) {
            throw new \InvalidArgumentException("El número de meses debe ser positivo.");
        }

        $strategy = $this->strategyFactory->getStrategy($contract->getPaymentMethod());
        $baseInstallmentAmount = $contract->getTotalValue() / $numberOfMonths;

        $response = new ProjectionResponse();
        $response->totalContractValue = (float)$contract->getTotalValue();

        for ($month = 1; $month <= $numberOfMonths; $month++) {
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
