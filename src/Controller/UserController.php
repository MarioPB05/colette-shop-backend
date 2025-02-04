<?php

namespace App\Controller;

use App\DTO\user\CreateUserRequest;
use App\DTO\user\ShowUserResponse;
use App\DTO\user\TableUserResponse;
use App\Entity\Client;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
final class UserController extends AbstractController{

    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function register(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload] CreateUserRequest $createUserRequest
    ): JsonResponse
    {
        $client = new Client();
        $client->setName($createUserRequest->name);
        $client->setSurname($createUserRequest->surname);

        try {
            $client->setBirthdate(new \DateTime($createUserRequest->birthdate));
        } catch (\DateMalformedStringException $e) {
            return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $client->setDni($createUserRequest->dni);

        $user = new User();
        $user->setUsername($createUserRequest->username);
        $user->setEmail($createUserRequest->email);
        $user->setPassword($passwordHasher->hashPassword($user, $createUserRequest->password));

        $tag = substr(bin2hex(random_bytes(8)), 0, 8);

        // Check if tag is already in use
        while ($entityManager->getRepository(User::class)->findOneBy(['brawl_tag' => $tag])) {
            $tag = substr(bin2hex(random_bytes(8)), 0, 8);
        }

        $user->setBrawlTag(strtoupper($tag));
        $user->setClient($client);
        $user->setRole(UserRole::USER);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'User created!'], Response::HTTP_CREATED);
    }

    #[Route('/', name: 'user_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {

        $users = $entityManager->getRepository(User::class)->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = new TableUserResponse(
                $user->getId(),
                $user->getClient()->getName(),
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
            $user->getGems(),
            $user->isEnabled(),
            $user->getBrawlerAvatar()->getImage()
        ), Response::HTTP_OK);
    }

}
