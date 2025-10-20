<?php
namespace App\DTO;
class ProjectionResponse
{
    /** @var InstallmentDetailResponse[] */
    public array $installments = [];
    public float $totalContractValue = 0;
    public float $totalInterestPaid = 0;
    public float $totalFeesPaid = 0;
    public float $totalPaidWithFeesAndInterest = 0;
}
