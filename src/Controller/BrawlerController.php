<?php

namespace App\Controller;

use App\DTO\brawler\TableBrawlerResponse;
use App\Repository\BrawlerRepository;
use App\Repository\UserBrawlerRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/brawlers')]
final class BrawlerController extends AbstractController
{

    #[Route('/', name: 'get_all_brawlers', methods: ['GET'])]
    public function getAllBrawlers(BrawlerRepository $brawlerRepository): JsonResponse
    {
        $brawlers = $brawlerRepository->findAll();

        $response = [];

        foreach ($brawlers as $brawler) {
            $response[] = new TableBrawlerResponse(
                $brawler->getId(),
                $brawler->getName(),
                $brawlerRepository->findCountBrawlersByPerson($brawler->getId()),
                $brawlerRepository->findCountPersonByFavouriteBrawler($brawler->getId()),
                $brawler->getPinImage(),
                $brawler->getRarity()->getName()
            );
        }

        return $this->json($response);

    }

}