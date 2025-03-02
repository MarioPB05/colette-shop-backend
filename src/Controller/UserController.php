<?php

namespace App\Controller;

use App\DTO\brawler\BrawlerUserDetailsResponse;
use App\DTO\brawler\UserBrawler;
use App\DTO\order\OrderUserDetailsResponse;
use App\DTO\user\ShowUserResponse;
use App\DTO\user\TableUserResponse;
use App\DTO\user\UserChangeRequest;
use App\DTO\user\UserDetailsResponse;
use App\Entity\User;
use App\Repository\BrawlerRepository;
use App\Repository\OrderRepository;
use App\Repository\UserBrawlerRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $userDetails = $userRepository->getUserDetails($user);

        if (!$userDetails) {
            return new JsonResponse(['message' => 'User details not found'], Response::HTTP_NOT_FOUND);
        }

        $newUserDetails = new UserDetailsResponse();
        $newUserDetails->id = $userDetails['id'];
        $newUserDetails->username = $userDetails['username'];
        $newUserDetails->brawlTag = $userDetails['brawl_tag'];
        $newUserDetails->name = $userDetails['name'];
        $newUserDetails->surname = $userDetails['surname'];
        $newUserDetails->birthDate = $userDetails['birthdate'];
        $newUserDetails->dni = $userDetails['dni'];
        $newUserDetails->email = $userDetails['email'];
        $newUserDetails->gems = $userDetails['gems'];
        $newUserDetails->openBoxes = $userDetails['open_boxes'];
        $newUserDetails->favouriteBrawlers = $userDetails['favourite_brawlers'];
        $newUserDetails->trophies = $userDetails['trophies'];
        $newUserDetails->brawlers = $userDetails['brawlers'];
        $newUserDetails->gifts = $userDetails['gifts'];

        $brawlerAvatar = new UserBrawler();
        $brawlerAvatar->id = $userDetails['brawler_avatar_id'];
        $brawlerAvatar->image = $userDetails['brawler_avatar_image'];
        $brawlerAvatar->pinImage = $userDetails['brawler_avatar_pin_image'];
        $brawlerAvatar->modelImage = $userDetails['brawler_avatar_model_image'];
        $brawlerAvatar->portraitImage = $userDetails['brawler_avatar_portrait_image'];
        $brawlerAvatar->name = $userDetails['brawler_avatar_name'];

        $newUserDetails->brawlerAvatar = $brawlerAvatar;

        return $this->json($newUserDetails);
    }

    #[Route('/details/brawlers', name: 'user_brawlers', methods: ['GET'])]
    public function getBrawlers(UserBrawlerRepository $userBrawlerRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $brawlers = $userBrawlerRepository->getBrawlers($user);

        $brawlerResponses = [];
        foreach ($brawlers as $brawler) {
            $brawlerResponse = new UserBrawler();
            $brawlerResponse->setId($brawler['id']);
            $brawlerResponse->setImage($brawler['image']);
            $brawlerResponse->setPinImage($brawler['pin_image']);
            $brawlerResponse->setModelImage($brawler['model_image']);
            $brawlerResponse->setPortraitImage($brawler['portrait_image']);
            $brawlerResponse->setName($brawler['name']);
            $brawlerResponses[] = $brawlerResponse;
        }

        return new JsonResponse($brawlerResponses, Response::HTTP_OK);
    }

    #[Route('/details/orders', name: 'user_orders', methods: ['GET'])]
    public function getOders(OrderRepository $orderRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_OK);
        }

        $orders = $orderRepository->getOrdersByUser($user);

        $ordersResponses = [];
        foreach ($orders as $order) {
            $orderResonses = new OrderUserDetailsResponse();
            $orderResonses->setId($order['id']);
            $orderResonses->setInvoiceNumber($order['invoice_number']);
            $orderResonses->setPurchaseDate($order['purchase_date']);
            $orderResonses->setUsername($order['username']);
            $orderResonses->setTotalItems($order['total_items']);
            $orderResonses->setDiscount($order['discount']);
            $orderResonses->setTotalPrice($order['total_price']);
            $orderResonses->setTotalWithDiscount($order['total_with_discount']);
            $orderResonses->setGiftUsername($order['gift_username']);
            $ordersResponses[] = $orderResonses;
        }

        return new JsonResponse($ordersResponses, Response::HTTP_OK);
    }

    #[Route('/details/brawler_image/{idBrawler}' , name: 'user_brawler_image', methods: ['POST'])]
    public function setBrawlerImage(int $idBrawler,BrawlerRepository $brawlerRepository ,EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_OK);
        }

        $brawler = $brawlerRepository->find($idBrawler);

        $user->setBrawlerAvatar($brawler);
        $entityManager->flush();

        return new JsonResponse(['status' => true], Response::HTTP_OK);
    }

    /**
     * @throws \DateMalformedStringException
     */
    #[Route('/details/change_details', name: 'user_change_details', methods: ['PUT'])]
    public function setUserDetails(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        $userChangeRequest = new UserChangeRequest();
        $userChangeRequest->setName($data['name']);
        $userChangeRequest->setSurname($data['surname']);
        $userChangeRequest->setBirthDate($data['birthDate']);
        $userChangeRequest->setDni($data['dni']);
        $userChangeRequest->setEmail($data['email']);

        $user->getClient()->setName($userChangeRequest->name);
        $user->getClient()->setSurname($userChangeRequest->surname);
        $user->getClient()->setBirthdate(new DateTime($userChangeRequest->birthDate));
        $user->getClient()->setDni($userChangeRequest->dni);
        $user->setEmail($userChangeRequest->email);

        $entityManager->flush();

        return new JsonResponse(['status' => true], Response::HTTP_OK);
    }

}