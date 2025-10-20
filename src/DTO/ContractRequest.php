<?php
namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;

class ContractRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public ?string $contractNumber = null;

    #[Assert\NotBlank]
    #[Assert\Type(\DateTimeImmutable::class)]
    public ?\DateTimeImmutable $contractDate = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?float $totalValue = null;

    #[Assert\NotBlank]
    #[Assert\Choice(['paypal', 'payonline'])]
    public ?string $paymentMethod = null;
}
