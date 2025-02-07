<?php

namespace App\Controller;

use App\Repository\InventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stats')]
final class StatsController extends AbstractController
{

    #[Route('/inventory', name: 'get_inventory_stat', methods: ['GET'])]
    public function inventoryStats(InventoryRepository $inventoryRepository): JsonResponse
    {
        $inventory = $inventoryRepository->inventoryStats();

        return $this->json($inventory);
    }

}
