<?php

namespace App\Tests\Service;

use App\DTO\ContractRequest;
use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Service\ContractService;
use App\Service\PaymentStrategyFactory;
use App\Service\PaymentStrategy\PaymentStrategyInterface;
use App\ValueObject\Money;
use App\ValueObject\PaymentMethod;
use PHPUnit\Framework\TestCase;

class ContractServiceTest extends TestCase
{
    private $contractRepositoryMock;
    private $strategyFactoryMock;
    private $contractService;

    protected function setUp(): void
    {
        // 1. Mockear las dependencias
        $this->contractRepositoryMock = $this->createMock(ContractRepository::class);
        $this->strategyFactoryMock = $this->createMock(PaymentStrategyFactory::class);

        // 2. Instanciar el Servicio con las dependencias simuladas
        $this->contractService = new ContractService(
            $this->contractRepositoryMock,
            $this->strategyFactoryMock
        );
    }

    public function testCreateContractSavesContractWithCorrectData()
    {
        $request = new ContractRequest(
            contractNumber: 'C-001',
            contractDate: new \DateTimeImmutable('2025-01-01'), // <-- ¡Aquí está el problema!
            totalValue: 1200.00,
            paymentMethod: 'payonline'
        );

        // Esperamos que el método 'save' del repositorio sea llamado exactamente una vez.
        $this->contractRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Contract $contract) {
                // Verificamos que los Value Objects se hayan instanciado correctamente
                $this->assertEquals('C-001', $contract->getContractNumber());
                $this->assertEquals(1200.00, $contract->getTotalValue()->getAmount());
                $this->assertEquals('payonline', $contract->getPaymentMethod()->getValue());
                return true; // El mock pasa si todas las aserciones internas son correctas
            }), true);

        // Ejecutar el método
        $contract = $this->contractService->createContract($request);

        // Verificación final del objeto devuelto
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('C-001', $contract->getContractNumber());
    }


    public function testProjectInstallmentsReturnsProjectionResponse()
    {
        // Preparación de datos y mocks
        $contractId = 1;
        $numberOfMonths = 2;
        $totalValue = 1000.00;
        $baseInstallmentAmount = 500.00; // 1000 / 2

        // Crear la entidad Contrato (simulando lo que devuelve find)
        $contract = (new Contract())
            ->setTotalValue(new Money($totalValue))
            ->setPaymentMethod(new PaymentMethod('payonline'))
            ->setContractDate(new \DateTimeImmutable('2025-10-20'));

        // Mockear el Repositorio: Simular la búsqueda del contrato
        $this->contractRepositoryMock->expects($this->once())
            ->method('find')
            ->with($contractId)
            ->willReturn($contract);

        // Mockear la Estrategia de Pago: Simular el cálculo para cada cuota
        $strategyMock = $this->createMock(PaymentStrategyInterface::class);
        $calculationResult = [
            'amount' => 495.00,  // Base - Fee
            'interest' => 10.00, // Interés simulado
            'fee' => 5.00,      // Comisión simulada
            'total' => 510.00    // Total por cuota
        ];

        // Esperamos que el cálculo sea llamado 2 veces (una por mes)
        $strategyMock->expects($this->exactly($numberOfMonths))
            ->method('calculateInstallment')
            ->with($baseInstallmentAmount) // Se llama con el monto base
            ->willReturn($calculationResult);

        // Mockear la Factory: Simular la obtención de la estrategia
        $this->strategyFactoryMock->expects($this->once())
            ->method('getStrategy')
            ->with('payonline') // Verifica que use el valor del PaymentMethod
            ->willReturn($strategyMock);


        //Ejecutar la proyección
        $response = $this->contractService->projectInstallments($contractId, $numberOfMonths);

        // Verificación de la respuesta
        $this->assertNotNull($response);
        $this->assertEquals($totalValue, $response->totalContractValue);
        $this->assertCount(2, $response->installments);

        // Verificación de totales calculados (2 cuotas * 10 interés + 2 cuotas * 5 fee)
        $this->assertEquals(20.00, $response->totalInterestPaid);
        $this->assertEquals(10.00, $response->totalFeesPaid);
        // 1000 (valor total) + 20 (interés) + 10 (fees) = 1030
        $this->assertEquals(1030.00, $response->totalPaidWithFeesAndInterest);
    }

    public function testProjectInstallmentsReturnsNullIfContractIsNotFound()
    {
        // El repositorio devuelve null
        $this->contractRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn(null);

        // Debe devolver null
        $this->assertNull($this->contractService->projectInstallments(999, 10));
    }

    public function testProjectInstallmentsThrowsExceptionForZeroMonths()
    {
        $contract = (new Contract())
            ->setTotalValue(new Money(100.00))
            ->setPaymentMethod(new PaymentMethod('paypal'))
            ->setContractDate(new \DateTimeImmutable());

        $this->contractRepositoryMock->method('find')->willReturn($contract);

        // Debe lanzar una excepción de Argumento Inválido
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El número de meses debe ser positivo.');

        // ACT
        $this->contractService->projectInstallments(1, 0);
    }
}
