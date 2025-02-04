<?php

namespace App\Controller;

use App\DTO\box\TableBoxResponse;
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
    public function getAllShop(): JsonResponse
    {
        return getAllBoxesShop();
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
