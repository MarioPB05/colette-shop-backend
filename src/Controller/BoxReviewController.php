<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BoxReviewRepository;
use App\Repository\GemTransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/reviews')]
final class BoxReviewController extends AbstractController
{
    #[Route('/box/{boxId}', name: 'get_box_reviews', methods: ['GET'])]
    public function getBoxReviews(int $boxId, BoxReviewRepository $boxReviewRepository): JsonResponse
    {
        $reviews = $boxReviewRepository->getBoxReviews($boxId);

        return $this->json($reviews);
    }

    #[Route('/box/{boxId}/user', name: 'user_has_reviewed_box', methods: ['GET'])]
    public function userHasReviewedBox(int $boxId, BoxReviewRepository $boxReviewRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $hasReviewed = $boxReviewRepository->userHasReviewedBox($boxId, $user->getId());

        return $this->json(['hasReviewed' => $hasReviewed]);
    }

    #[Route('/box/{boxId}/user', name: 'add_box_review', methods: ['POST'])]
    public function addBoxReview(int $boxId, BoxReviewRepository $boxReviewRepository, Request $request, GemTransactionRepository $gemTransactionRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $hasReviewed = $boxReviewRepository->userHasReviewedBox($boxId, $user->getId());
        if ($hasReviewed) {
            return $this->json(['status' => 'error', 'message' => 'You have already reviewed this box'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $rating = $data['rating'];
        $comment = $data['comment'];

        $boxReviewRepository->addBoxReview($boxId, $user->getId(), $rating, $comment);
        $gemTransactionRepository->addGemTransaction($user->getId(), 15);
        return $this->json(['status' => 'success']);
    }

}