<?php

namespace App\Controller;

use App\DTO\box\TableBoxResponse;
use App\Repository\BoxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/boxes')]
final class BoxController extends AbstractController{

    #[Route('/', name: 'box_get_all', methods: ['GET'])]
    public function getAll(BoxRepository $boxRepository, TranslatorInterface $translator): JsonResponse
    {
        return $this->json(array_map(fn($result) => new TableBoxResponse(
            $result['id'],
            $result['name'],
            $result['price'],
            $result['quantity'],
            $result['type'],
            $result['pinned'],
            $translator
        ), $boxRepository->getAllBoxes()));
    }

}
