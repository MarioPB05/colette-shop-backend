<?php

namespace App\Controller;

use App\DTO\box\BoxCartRequest;
use App\DTO\box\BoxCartResponse;
use App\DTO\box\BoxDetailResponse;
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
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
            $result['boxes_left'],
            $result['favorite_brawlers_in_box'],
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
            $result['favorite_brawlers_in_box'],
            $result['repeat_every_hours'],
            $result['claimed'],
            $result['last_claimed'],
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

    #[Route('/cart', name: 'box_get_from_cart', methods: ['POST'])]
    public function getBoxesFromCart(#[MapRequestPayload] BoxCartRequest $request, BoxRepository $boxRepository, TranslatorInterface $translator): JsonResponse
    {
        // Flat all box id in array
        $boxIds = array_map(fn($item) => $item->boxId, $request->items);

        // Get all boxes from database
        $boxes = $boxRepository->getAllBoxDetails($boxIds);

        /** @var BoxCartResponse[] $boxes */
        $response = [];

        foreach ($boxes as $box) {
            $item = array_values(array_filter($request->items, fn($item) => $item->boxId === $box['id']))[0];

            // Check if it has enough quantity in database
            if ($box['boxes_left'] != -1 && $box['boxes_left'] < $item->quantity) {
                // Set quantity to maximum available, or if not available, remove from cart
                $item->quantity = $box['boxes_left'];
                if ($item->quantity === 0) {
                    continue;
                }
            }

            // Check if box is deleted
            if ($box['deleted']) {
                continue;
            }

            $response[] = new BoxCartResponse(
                $box['id'],
                $box['name'],
                $box['type'],
                $box['price'],
                $item->quantity,
                $box['price'] * $item->quantity,
                $box['boxes_left'],
                $box['is_daily'],
                $box['claimed'],
                $translator
            );
        }

        return $this->json($response);
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

    #[Route('/{id}', name: 'box_get_details', methods: ['GET'])]
    public function getDetails(int $id, BoxRepository $boxRepository, TranslatorInterface $translator): JsonResponse
    {
        $boxDetails = $boxRepository->getBoxDetails($id);

        return $this->json(new BoxDetailResponse(
            $boxDetails['id'],
            $boxDetails['name'],
            $boxDetails['price'],
            $boxDetails['type'],
            $boxDetails['boxes_left'],
            $boxDetails['brawler_quantity'],
            $boxDetails['is_daily'],
            $boxDetails['claimed'],
            $translator
        ));
    }

}
