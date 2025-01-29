<?php

namespace App\Controller;

use App\Repository\BoxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/box')]
final class BoxController extends AbstractController{

    #[Route('/', name: 'box_get_all', methods: ['GET'])]
    public function getAll(BoxRepository $boxRepository): JsonResponse
    {
        return $this->json($boxRepository->getAllBoxes());
    }

}
