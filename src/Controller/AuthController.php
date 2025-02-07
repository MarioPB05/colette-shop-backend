<?php

namespace App\Controller;

use App\DTO\user\CreateUserRequest;
use App\Entity\Client;
use App\Entity\User;
use App\Enum\UserRole;
use DateMalformedStringException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
final class AuthController extends AbstractController
{

    #[Route('/verify-user', name: 'app_auth_verify_user', methods: ['GET'])]
    public function user(): JsonResponse
    {
        $valid = $this->isGranted('ROLE_USER');

        return new JsonResponse(['valid' => $valid]);
    }

    #[Route('/verify-admin', name: 'app_auth_verify_admin', methods: ['GET'])]
    public function admin(): JsonResponse
    {
        $valid = $this->isGranted('ROLE_ADMIN');

        return new JsonResponse(['valid' => $valid]);
    }

    #[Route('/verify', name: 'app_auth_verify_token', methods: ['GET'])]
    public function isAuthenticated(): JsonResponse
    {
        $valid = $this->isGranted('IS_AUTHENTICATED_FULLY');

        return new JsonResponse(['valid' => $valid]);
    }

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
            $client->setBirthdate(new DateTime($createUserRequest->birthdate));
        } catch (DateMalformedStringException $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Fecha de nacimiento inválida'], Response::HTTP_BAD_REQUEST);
        }

        $client->setDni($createUserRequest->dni);

        $user = new User();
        $user->setUsername($createUserRequest->username);
        $user->setEmail($createUserRequest->email);
        $user->setPassword($passwordHasher->hashPassword($user, $createUserRequest->password));

        try {
            $tag = substr(bin2hex(random_bytes(8)), 0, 8);

            // Check if tag is already in use
            while ($entityManager->getRepository(User::class)->findOneBy(['brawl_tag' => $tag])) {
                $tag = substr(bin2hex(random_bytes(8)), 0, 8);
            }
        } catch (RandomException $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Error al generar tu Brawl Tag'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user->setBrawlTag(strtoupper($tag));
        $user->setClient($client);
        $user->setRole(UserRole::USER);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success'], Response::HTTP_CREATED);
    }

}
