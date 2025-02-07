<?php

namespace App\Controller;

use App\DTO\box\BoxShopResponse;
use App\DTO\box\DailyBoxShopResponse;
use App\DTO\box\TableBoxResponse;
use App\Entity\User;
use App\Entity\Box;
use App\Repository\BoxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/list-daily', name: 'box_get_all_free_daily', methods: ['GET'])]
    public function getAllFreeDailyBoxes(BoxRepository $boxRepository, TranslatorInterface $translator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(array_map(fn($result) => new DailyBoxShopResponse(
            $result['id'],
            $result['name'],
            $result['type'],
            $result['favoritebrawlersinbox'],
            $result['repeathours'],
            $result['claimed'],
            $translator
        ), $boxRepository->getAllFreeDailyBoxesShop($user)));
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

    #[Route('/{id}', name: 'box_remove', methods: ['DELETE'])]
    public function remove(Box $box, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($box->isDeleted()) {
            return $this->json(['message' => 'Box already removed'], Response::HTTP_BAD_REQUEST);
        }

        $box->setDeleted(true);
        $entityManager->flush();

        return $this->json(['result' => true]);
    }

}
