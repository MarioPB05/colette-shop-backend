<?php

namespace App\Controller;

use App\DTO\box\BoxInventoryDetailsResponse;
use App\DTO\box\InventoryBoxResponse;
use App\Entity\User;
use App\Enum\BoxType;
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
        /* @var User $user */
        $user = $this->getUser();

        $result = $inventoryRepository->getInventoryBox($id, $user->getId());

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

    #[Route("/user/inventory", name: 'user_inventory', methods: ['GET'])]
    public function getUserInventory(InventoryRepository $inventoryRepository, TranslatorInterface $translator): JsonResponse
    {
        /* @var User $user */
        $user = $this->getUser();

        $result = $inventoryRepository->getInventoryForUser($user);

        $inventoryRequests = [];
        foreach ($result as $inventory) {
            $inventoryRequest = new BoxInventoryDetailsResponse();
            $inventoryRequest->setInventoryId($inventory['inventory_id']);
            $inventoryRequest->setOpen($inventory['open']);
            $inventoryRequest->setCollectDate($inventory['collect_date']);
            $inventoryRequest->setOpenDate($inventory['open_date']);
            $inventoryRequest->setBoxId($inventory['box_id']);
            $inventoryRequest->setBoxName($inventory['box_name']);
            $inventoryRequest->setTotalBrawlers($inventory['total_brawlers']);
            $inventoryRequest->setNewBrawlersObtained($inventory['new_brawlers_obtained']);
            $inventoryRequest->setTotalTrophies($inventory['total_trophies']);
            $inventoryRequest->setGiftFrom($inventory['gift_from']);
            $inventoryRequest->setBoxType($translator->trans('BoxType.' . BoxType::tryFrom($inventory['box_type'])->name, domain: 'enums'));
            $inventoryRequests[] = $inventoryRequest;
        }

        return $this->json($inventoryRequests);
    }
}