<?php

namespace App\Controller;

use App\DTO\box\BoxShopResponse;
use App\DTO\box\TableBoxResponse;
use App\Entity\User;
use App\Repository\BoxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/boxes')]
final class BoxController extends AbstractController
{
    #[Route('/list', name: 'box_get_all_shop', methods: ['GET'])]
    public function getAllShop(BoxRepository $boxRepository, TranslatorInterface $translator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(array_map(fn($result) => new BoxShopResponse(
            $result['id'],
            $result['name'],
            $result['price'],
            $result['type'],
            $result['boxesleft'],
            $result['favoritebrawlersinbox'],
            $result['pinned'],
            $result['popular'],
            $translator
        ), $boxRepository->getAllBoxesShop($user)));
    }

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
