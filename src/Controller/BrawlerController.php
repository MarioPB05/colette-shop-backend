<?php

namespace App\Controller;

use App\DTO\brawler\TableBrawlerResponse;
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

}