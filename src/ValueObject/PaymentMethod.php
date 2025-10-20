<?php

namespace App\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
class PaymentMethod
{
    public const PAYPAL = 'paypal';
    public const PAYONLINE = 'payonline';
    private const VALID_METHODS = [self::PAYPAL, self::PAYONLINE];

    #[ORM\Column(type: 'string', length: 50)]
    private readonly string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, self::VALID_METHODS, 'Método de pago inválido: %s');
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
