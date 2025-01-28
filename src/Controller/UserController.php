<?php

namespace App\Controller;

use App\DTO\user\CreateUserRequest;
use App\Entity\Client;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
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

}
