<?php

namespace App\Tests\Service\PaymentStrategy;

use App\Service\PaymentStrategy\PayOnlineStrategy;
use PHPUnit\Framework\TestCase;

class PayOnlineStrategyTest extends TestCase
{
    public function testCalculateInstallment()
    {
        $strategy = new PayOnlineStrategy();

        // Base amount: 500.00
        // Interest: 2% (0.02) -> 10.00
        // Fee: 1% (0.01) -> 5.00
        // Total: 500.00 + 10.00 + 5.00 = 515.00
        $result = $strategy->calculateInstallment(500.00);

        $this->assertIsArray($result);

        $this->assertEquals(500.00, $result['amount']);
        $this->assertEquals(10.00, $result['interest']);
        $this->assertEquals(5.00, $result['fee']);
        $this->assertEquals(515.00, $result['total']);

        $resultDecimal = $strategy->calculateInstallment(100.55);
        $this->assertEquals(2.01, $resultDecimal['interest']); // 2.011 redondea a 2.01
        $this->assertEquals(1.01, $resultDecimal['fee']);      // 1.0055 redondea a 1.01
        $this->assertEquals(103.57, $resultDecimal['total']);  // 100.55 + 2.01 + 1.01
    }

    public function testGetIdentifierReturnsCorrectString()
    {
        $strategy = new PayOnlineStrategy();
        $this->assertEquals('payonline', $strategy->getIdentifier());
    }
}
