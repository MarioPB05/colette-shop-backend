<?php

namespace App\Controller;

use App\DTO\brawler\BrawlerUserDetailsResponse;
use App\DTO\user\ShowUserResponse;
use App\DTO\user\TableUserResponse;
use App\DTO\user\UserDetailsResponse;
use App\Entity\User;
use App\Repository\UserBrawlerRepository;
use App\Repository\UserRepository;
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

    #[Route('/find/{brawlTag}', name: 'user_show', methods: ['GET'])]
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

    #[Route('/details', name: 'user_details', methods: ['GET'])]
    public function getUserDetails(UserRepository $userRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_OK);
        }

        $userDetails = $userRepository->getUserDetails($user);

        $newUserDetails = new UserDetailsResponse();
        $newUserDetails->setId($userDetails['id']);
        $newUserDetails->setUsername($userDetails['username']);
        $newUserDetails->setBrawlTag($userDetails['brawltag']);
        $newUserDetails->setName($userDetails['name']);
        $newUserDetails->setSurname($userDetails['surname']);
        $newUserDetails->setBirthDate($userDetails['birthdate']);
        $newUserDetails->setDni($userDetails['dni']);
        $newUserDetails->setEmail($userDetails['email']);
        $newUserDetails->setGems($userDetails['gems']);
        $newUserDetails->setOpenBoxes($userDetails['openboxes']);
        $newUserDetails->setFavouriteBrawlers($userDetails['favouritebrawlers']);
        $newUserDetails->setTrophies($userDetails['trophies']);
        $newUserDetails->setBrawlers($userDetails['brawlers']);
        $newUserDetails->setGifts($userDetails['gifts']);
        $newUserDetails->setBrawlerAvatar($userDetails['brawleravatar']);

        return new JsonResponse($newUserDetails, Response::HTTP_OK);
    }

    #[Route('/details/brawlers', name: 'user_brawlers', methods: ['GET'])]
    public function getBrawlers(UserBrawlerRepository $userBrawlerRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_OK);
        }

        $brawlers = $userBrawlerRepository->getBrawlers($user);

        for ($i = 0; $i < count($brawlers); $i++) {
            $brawlers[$i] = new BrawlerUserDetailsResponse();
            $brawlers[$i]->setBrawlerId($brawlers[$i]['brawler_id']);
            $brawlers[$i]->setName($brawlers[$i]['name']);
            $brawlers[$i]->setImage($brawlers[$i]['image']);
            $brawlers[$i]->setModelImage($brawlers[$i]['model_image']);
        }

        return new JsonResponse($brawlers, Response::HTTP_OK);
    }
}