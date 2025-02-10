<?php

namespace App\Controller;

use App\DTO\user\ShowUserResponse;
use App\DTO\user\TableUserResponse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
final class UserController extends AbstractController{

    #[Route('/', name: 'user_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {

        $users = $entityManager->getRepository(User::class)->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = new TableUserResponse(
                $user->getId(),
                $user->getClient()->getName() . ' ' . $user->getClient()->getSurname(),
                $user->getUsername(),
                $user->getBrawlTag(),
                $user->getEmail(),
                $user->getGems(),
                $user->isEnabled()
            );
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/disable/{id}', name: 'user_disable', methods: ['PUT'])]
    public function disableUser(EntityManagerInterface $entityManager, User $user): JsonResponse
    {
        $user->setEnabled(false);
        $entityManager->flush();

        return new JsonResponse(['status' => true], Response::HTTP_OK);
    }

    #[Route('/enable/{id}', name: 'user_enable', methods: ['PUT'])]
    public function enableUser(EntityManagerInterface $entityManager, User $user): JsonResponse
    {
        $user->setEnabled(true);
        $entityManager->flush();

        return new JsonResponse(['status' => true], Response::HTTP_OK);
    }

    #[Route('/{brawlTag}', name: 'user_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['brawlTag' => 'brawl_tag'])] User $user): JsonResponse
    {
        return new JsonResponse(new ShowUserResponse(
            $user->getId(),
            $user->getClient()->getName(),
            $user->getClient()->getSurname(),
            $user->getBrawlTag(),
            $user->getUsername(),
            $user->getEmail(),
            $user->getClient()->getDni(),
            $user->getClient()->getBirthdate()->format('Y-m-d'),
            $user->isEnabled(),
            $user->getBrawlerAvatar()->getImage()
        ), Response::HTTP_OK);
    }

}
