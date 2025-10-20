<?php

namespace App\DTO;

use App\ValueObject\PaymentMethod;
use Symfony\Component\Validator\Constraints as Assert;

class ContractRequest
{
    // Usamos el constructor para inicializar las propiedades
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public ?string $contractNumber = null,

        #[Assert\NotBlank]
        #[Assert\Type(\DateTimeImmutable::class)]
                       // ¡El nombre del parámetro ($contractDate) ahora está definido formalmente!
        public ?\DateTimeImmutable $contractDate = null,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public ?float $totalValue = null,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: [PaymentMethod::PAYPAL, PaymentMethod::PAYONLINE])]
        public ?string $paymentMethod = null
    ) {
    }
}
