<?php

namespace App\Controller;

use App\DTO\box\BoxDetailResponse;
use App\Repository\BoxRepository;
use App\Repository\BoxReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/reviews')]
final class BoxReviewController extends AbstractController
{
    #[Route('/box/{boxId}', name: 'get_box_reviews', methods: ['GET'])]
    public function getBoxReviews(int $boxId, BoxReviewRepository $boxReviewRepository): JsonResponse
    {
        $reviews = $boxReviewRepository->getBoxReviews($boxId);

        return $this->json($reviews);
    }

}