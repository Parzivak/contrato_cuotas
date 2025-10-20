<?php
namespace App\DTO;
class InstallmentDetailResponse
{
    public function __construct(
        public readonly int $monthNumber,
        public readonly string $dueDate,
        public readonly float $baseAmount,
        public readonly float $interest,
        public readonly float $fee,
        public readonly float $totalAmount
    ) {}
}
