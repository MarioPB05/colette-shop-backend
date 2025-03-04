<?php

namespace App\Controller;

use App\DTO\box\BoxDetailResponse;
use App\DTO\box\BoxShopResponse;
use App\DTO\box\CreateBoxRequest;
use App\DTO\box\CreateDailyBoxRequest;
use App\DTO\box\DailyBoxShopResponse;
use App\DTO\box\TableBoxResponse;
use App\Entity\User;
use App\Entity\Box;
use App\Repository\BoxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/', name: 'box_create', methods: ['POST'])]
    public function create(BoxRepository $boxRepository, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $createBoxRequest = new CreateBoxRequest(
            $data['name'],
            $data['price'],
            $data['type'],
            $data['quantity'],
            $data['brawler_quantity'],
            $data['brawlers_in_box']
        );

        try {
            $boxRepository->createBox($createBoxRequest);
        }catch (\Exception $e){
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }


        return $this->json(['status' => 'success', 'message' => 'Box created'], Response::HTTP_CREATED);
    }

    #[Route('/daily', name: 'box_create_daily', methods: ['POST'])]
    public function createDaily(BoxRepository $boxRepository, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $createBoxRequest = new CreateDailyBoxRequest(
            $data['name'],
            $data['type'],
            $data['repeat_every_hours'],
            $data['brawler_quantity'],
            $data['brawlers_in_box']
        );

        try {
            $boxRepository->createDailyBox($createBoxRequest);
        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'success', 'message' => 'Box created'], Response::HTTP_CREATED);
    }
}
