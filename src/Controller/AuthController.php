<?php

namespace App\Controller;

use App\DTO\user\CreateUserRequest;
use App\Entity\Brawler;
use App\Entity\Client;
use App\Entity\GemTransaction;
use App\Entity\User;
use App\Enum\UserRole;
use DateMalformedStringException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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

    #[Route('/verify-username/{username}', name: 'app_auth_verify_username', methods: ['GET'])]
    public function verifyUsername(EntityManagerInterface $entityManager, string $username): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        return new JsonResponse(['exists' => $user !== null]);
    }

    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function register(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $JWTManager,
        MailerInterface  $mailer,
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
        $user->setEnabled(false); // Required to verify email
        $user->setUsername($createUserRequest->username);
        $user->setEmail($createUserRequest->email);
        $user->setPassword($passwordHasher->hashPassword($user, $createUserRequest->password));

        try {
            $tag = substr(bin2hex(random_bytes(9)), 0, 9);

            // Check if tag is already in use
            while ($entityManager->getRepository(User::class)->findOneBy(['brawl_tag' => $tag])) {
                $tag = substr(bin2hex(random_bytes(9)), 0, 9);
            }
        } catch (RandomException $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Error al generar tu Brawl Tag'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $defaultBrawler = $entityManager->getRepository(Brawler::class)->findOneBy(['id' => 16000000]);

        if ($defaultBrawler === null) {
            return new JsonResponse(['status' => 'error', 'message' => 'Error al obtener el Brawler por defecto'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user->setBrawlTag(strtoupper($tag));
        $user->setClient($client);
        $user->setGems(200);
        $user->setRole(UserRole::USER);
        $user->setBrawlerAvatar($defaultBrawler);

        $entityManager->persist($user);
        $entityManager->flush();

        $gemTransaction = new GemTransaction();
        $gemTransaction->setGems(200);
        $gemTransaction->setDate(new DateTime());
        $gemTransaction->setUser($user);

        $entityManager->persist($gemTransaction);
        $entityManager->flush();

        try {
            $payload = [
                'username_id' => $user->getId(),
                'exp' => (new DateTime())->modify('+1 day')->getTimestamp()
            ];

            $verifyToken = $JWTManager->createFromPayload($user, $payload);

            if (empty($verifyToken)) {
                return new JsonResponse(['status' => 'error', 'message' => 'Error al generar el token'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $email = (new TemplatedEmail())
                ->from('developers.daw.seville@gmail.com')
                ->to($user->getEmail())
                ->subject('Bienvenido a Colette\'s Shop | Verifica tu cuenta')
                ->htmlTemplate('verify-email.html.twig')
                ->context([
                    'username' => $user->getUsername(),
                    'token' => $verifyToken
                ]);

            $mailer->send($email);

            return new JsonResponse(['status' => 'success'], Response::HTTP_CREATED);
        } catch (DateMalformedStringException $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Error al generar el token'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Error al enviar el correo'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/verify-email', name: 'app_auth_verify_email_token', methods: ['GET'])]
    public function verifyEmail(Request $request, EntityManagerInterface $entityManager, JWTTokenManagerInterface $JWTManager): JsonResponse {
        /** @var TokenInterface $token */
        $token = $request->query->get('token');

        if (empty($token)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Token no encontrado'], Response::HTTP_BAD_REQUEST);
        }

        $payload = $JWTManager->parse($token);

        if (count($payload) === 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'Token inválido'], Response::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->find($payload['username_id']);

        if ($user === null) {
            return new JsonResponse(['status' => 'error', 'message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        if ($user->isEnabled()) {
            return new JsonResponse(['status' => 'error', 'message' => 'El usuario ya ha sido verificado'], Response::HTTP_BAD_REQUEST);
        }

        $user->setEnabled(true);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }

}
