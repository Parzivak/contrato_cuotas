<?php

namespace App\Tests\Service\PaymentStrategy;

use App\Service\PaymentStrategy\PayPalStrategy;
use PHPUnit\Framework\TestCase;

class PayPalStrategyTest extends TestCase
{
    public function testCalculateInstallment()
    {
        $strategy = new PayPalStrategy();

        // Base amount: 500.00
        // Interest: 1% (0.01) -> 5.00
        // Fee: 2% (0.02) -> 10.00
        // Total: 500.00 + 5.00 + 10.00 = 515.00
        $result = $strategy->calculateInstallment(500.00);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('amount', $result);

        $this->assertEquals(500.00, $result['amount']);
        $this->assertEquals(5.00, $result['interest']);
        $this->assertEquals(10.00, $result['fee']);
        $this->assertEquals(515.00, $result['total']);

        // Test with a base amount resulting in decimals (100.55 * 0.01)
        $resultDecimal = $strategy->calculateInstallment(100.55);
        $this->assertEquals(1.01, $resultDecimal['interest']); // 1.0055 redondea a 1.01
        $this->assertEquals(2.01, $resultDecimal['fee']);      // 2.011 redondea a 2.01
        $this->assertEquals(103.57, $resultDecimal['total']);  // 100.55 + 1.01 + 2.01
    }

    public function testGetIdentifierReturnsCorrectString()
    {
        $strategy = new PayPalStrategy();
        $this->assertEquals('paypal', $strategy->getIdentifier());
    }
}
