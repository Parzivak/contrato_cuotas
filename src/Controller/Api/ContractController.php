<?php
namespace App\Controller\Api;

use App\DTO\ContractRequest;
use App\Service\ContractService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/contracts')]
class ContractController extends AbstractController
{
    public function __construct(
        private readonly ContractService $contractService,
    ) {}

    #[Route('', name: 'api_contract_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ContractRequest $request): JsonResponse
    {
        $contract = $this->contractService->createContract($request);
        return $this->json([
            'id' => $contract->getId(),
            'contractNumber' => $contract->getContractNumber()
        ], Response::HTTP_CREATED);
    }

    // Proyectar Cuotas
    #[Route('/{id}/project-installments', name: 'api_contract_project', methods: ['POST'])]
    public function project(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $numberOfMonths = $data['numberOfMonths'] ?? 0;

        if ($numberOfMonths <= 0) {
            return $this->json(['message' => 'numberOfMonths es requerido y debe ser positivo'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $projection = $this->contractService->projectInstallments($id, $numberOfMonths);
            if (!$projection) {
                return $this->json(['message' => 'Contrato no encontrado'], Response::HTTP_NOT_FOUND);
            }
            return $this->json($projection);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
