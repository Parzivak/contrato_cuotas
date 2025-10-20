<?php

namespace App\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
class Money
{
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private readonly string $amount;

    public function __construct(float $amount)
    {
        Assert::greaterThanEq($amount, 0, 'El valor monetario no puede ser negativo.');
        $this->amount = number_format($amount, 2, '.', '');
    }

    public function getAmount(): float
    {
        return (float) $this->amount;
    }
}
