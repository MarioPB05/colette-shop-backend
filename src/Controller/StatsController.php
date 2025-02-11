<?php

namespace App\Controller;

use App\DTO\stat\GemStatResponse;
use App\DTO\stat\InventoryStatResponse;
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

        return $this->json(array_map(fn($stat) => new InventoryStatResponse(
            $stat['day'],
            $stat['boxes'],
            $stat['total_price']
        ), $inventory)

        );
    }

    #[Route('/gems', name: 'get_gems_stat', methods: ['GET'])]
    public function gemsStats(InventoryRepository $inventoryRepository): JsonResponse
    {
        $inventory = $inventoryRepository->gemStat();

        return $this->json(
            array_map(fn($stat) => new GemStatResponse(
                $stat['day'],
                $stat['gems']
            ), $inventory)
        );
    }

}
