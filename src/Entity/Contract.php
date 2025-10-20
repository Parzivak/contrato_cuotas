<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use App\ValueObject\Money;
use App\ValueObject\PaymentMethod;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $contractNumber = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $contractDate = null;

    #[ORM\Embedded(class: Money::class)]
    private ?Money $totalValue = null;

    #[ORM\Embedded(class: PaymentMethod::class)]
    private ?PaymentMethod $paymentMethod = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContractNumber(): ?string
    {
        return $this->contractNumber;
    }

    public function setContractNumber(string $contractNumber): static
    {
        $this->contractNumber = $contractNumber;

        return $this;
    }

    public function getContractDate(): ?\DateTimeImmutable
    {
        return $this->contractDate;
    }

    public function setContractDate(\DateTimeImmutable $contractDate): static
    {
        $this->contractDate = $contractDate;

        return $this;
    }

    public function getTotalValue(): ?Money
    {
        return $this->totalValue;
    }

    public function setTotalValue(Money $totalValue): static
    {
        $this->totalValue = $totalValue;

        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getTotalValueAmount(): float
    {
        return $this->totalValue?->getAmount() ?? 0.0;
    }

    public function getPaymentMethodValue(): string
    {
        return $this->paymentMethod?->getValue() ?? '';
    }

    public function setTotalValueFromFloat(float $amount): static
    {
        $this->totalValue = new Money($amount);

        return $this;
    }

    public function setPaymentMethodFromString(string $method): static
    {
        $this->paymentMethod = new PaymentMethod($method);

        return $this;
    }
}
