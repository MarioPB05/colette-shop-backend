<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RarityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/rarity')]
class RarityController extends AbstractController
{
    public RarityRepository $rarityRepository;

    public function __construct(RarityRepository $rarityRepository)
    {
        $this->rarityRepository = $rarityRepository;
    }

    #[Route('/details', name: 'get_all_detail_rarities', methods: ['GET'])]
    public function getAllDetailRarities(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $rarities = $this->rarityRepository->getAllDetailRarities($user->getId());

        return $this->json($rarities);
    }
}