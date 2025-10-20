<?php

namespace App\Tests\Service;

use App\Service\PaymentStrategyFactory;
use App\Service\PaymentStrategy\PaymentStrategyInterface;
use PHPUnit\Framework\TestCase;

class PaymentStrategyFactoryTest extends TestCase
{
    /** @var PaymentStrategyInterface[] */
    private array $mockStrategies = [];

    protected function setUp(): void
    {
        // Crear Mocks de estrategias (simulando lo que Symfony haría con los Tags)
        $paypalMock = $this->createMock(PaymentStrategyInterface::class);
        $paypalMock->method('getIdentifier')->willReturn('paypal');

        $payonlineMock = $this->createMock(PaymentStrategyInterface::class);
        $payonlineMock->method('getIdentifier')->willReturn('payonline');

        $this->mockStrategies = [
            $paypalMock,
            $payonlineMock,
        ];
    }

    public function testGetStrategyReturnsCorrectStrategy()
    {
        //Instanciar la Factory con la colección de mocks
        $factory = new PaymentStrategyFactory($this->mockStrategies);

        //Obtener y verificar la estrategia 'paypal'
        $paypalStrategy = $factory->getStrategy('paypal');
        $this->assertInstanceOf(PaymentStrategyInterface::class, $paypalStrategy);
        $this->assertEquals('paypal', $paypalStrategy->getIdentifier());

        //Obtener y verificar la estrategia 'payonline'
        $payonlineStrategy = $factory->getStrategy('payonline');
        $this->assertInstanceOf(PaymentStrategyInterface::class, $payonlineStrategy);
        $this->assertEquals('payonline', $payonlineStrategy->getIdentifier());
    }

    public function testGetStrategyThrowsExceptionForUnknownIdentifier()
    {
        // ARRANGE
        $factory = new PaymentStrategyFactory($this->mockStrategies);

        // EXPECTATION
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Estrategia de pago desconocida: crypto');

        // ACT
        $factory->getStrategy('crypto');
    }
}
