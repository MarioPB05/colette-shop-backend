<?php

namespace App\Controller;

use App\DTO\brawler\BrawlerCardResponse;
use App\DTO\brawler\BrawlerProbabilityResponse;
use App\DTO\brawler\InventoryBrawlerResponse;
use App\DTO\brawler\UserBrawlerProbabilityResponse;
use App\Entity\User;
use App\Repository\BrawlerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/brawlers')]
final class BrawlerController extends AbstractController
{

    #[Route('/', name: 'get_all_brawlers', methods: ['GET'])]
    public function getAllBrawlers(BrawlerRepository $brawlerRepository): JsonResponse
    {
        $brawlers = $brawlerRepository->getAllBrawlers();

        return $this->json($brawlers);
    }

    #[Route('/list', name: 'get_all_brawlers_for_box_editor', methods: ['GET'])]
    public function getAllBrawlersForBoxEditor(BrawlerRepository $brawlerRepository): JsonResponse
    {
        $brawlers = $brawlerRepository->getAllBrawlersForBoxEditor();

        return $this->json($brawlers);
    }

    #[Route('/box/{boxId}', name: 'get_all_brawlers_probability_from_box', methods: ['GET'])]
    public function getAllBrawlersProbabilityByBox(int $boxId, BrawlerRepository $brawlerRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $brawlers = $brawlerRepository->getBrawlersProbabilityFromBox($boxId, $user);

        return $this->json(array_map(fn($result) => new BrawlerProbabilityResponse(
            $result['id'],
            $result['name'],
            $result['image'],
            $result['model_image'],
            $result['probability'],
            $result['user_quantity'],
            $result['rarity_id']
        ), $brawlers));
    }

    #[Route('/box/{box_id}/user', name: 'get_user_probability_brawlers_from_box', methods: ['GET'])]
    public function getUserProbabilityBrawlersFromBox(int $box_id, BrawlerRepository $brawlerRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $brawlers = $brawlerRepository->getUserProbabilityBrawlersFromBox($box_id, $user->getId());

        return $this->json(array_map(fn($result) => new UserBrawlerProbabilityResponse(
            $result['id'],
            $result['name'],
            $result['image'],
            $result['model_image'],
            $result['probability'],
            $result['user_quantity'],
            $result['rarity_id']
        ), $brawlers));
    }

    #[Route('/inventory/{item_id}', name: 'get_inventory_brawlers', methods: ['GET'])]
    public function getInventoryBrawlers(int $item_id, BrawlerRepository $brawlerRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $brawlers = $brawlerRepository->getInventoryBrawlers($user->getId(), $item_id);

        return $this->json(array_map(fn($result) => new InventoryBrawlerResponse(
            $result['id'],
            $result['name'],
            $result['image'],
            $result['user_quantity_actual'],
            $result['user_quantity_past']
        ), $brawlers));
    }

    #[Route('/user/collection', name: 'get_user_collection', methods: ['GET'])]
    public function getUserCollection(BrawlerRepository $brawlerRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $brawlers = $brawlerRepository->getBrawlerCards($user->getId());

        return $this->json(array_map(fn($result) => new BrawlerCardResponse(
            $result['id'],
            $result['name'],
            $result['model_image'],
            $result['rarity_id'],
            $result['rarity_color'],
            $result['user_quantity'],
            $result['user_favorite']
        ), $brawlers));
    }
}