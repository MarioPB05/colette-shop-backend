<?php

namespace App\Controller;

use App\DTO\box\InventoryBoxResponse;
use App\Repository\InventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/inventory')]
final class InventoryController extends AbstractController
{
    public InventoryRepository $inventoryRepository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    #[Route('/{id}', name: 'get_inventory_box', methods: ['GET'])]
    public function getInventoryBox(int $id, InventoryRepository $inventoryRepository, TranslatorInterface $translator): JsonResponse
    {
        $result = $inventoryRepository->getInventoryBox($id);

        if ($result) {
            print_r($result);
            return $this->json(new InventoryBoxResponse(
                $result['id'],
                $result['type']->getType(),
                $result['brawler_quantity'],
                $result['open'],
                $translator
            ));
        }

        return $this->json(['message' => 'Box not found'], Response::HTTP_NOT_FOUND);
    }
}