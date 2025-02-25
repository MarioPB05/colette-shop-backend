<?php

namespace App\Controller;

use App\DTO\box\InventoryBoxResponse;
use App\Entity\User;
use App\Repository\InventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

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
            return $this->json(new InventoryBoxResponse(
                $result['id'],
                $result['box_id'],
                $result['type'],
                $result['brawler_quantity'],
                $result['open'],
                $translator
            ));
        }

        return $this->json(['message' => 'Box not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route("/{id_item}/open", name: 'open_box', methods: ['POST'])]
    public function saveBoxOpenResults(int $id_item, Request $data, InventoryRepository $inventoryRepository): JsonResponse
    {
        /* @var User $user */
        $user = $this->getUser();

        $data = json_decode($data->getContent(), false);

        $result = $inventoryRepository->saveBoxOpenResults($id_item, $data, $user->getId());

        return $this->json([
            'message' => $result['message'],
        ], $result['code']);
    }
}