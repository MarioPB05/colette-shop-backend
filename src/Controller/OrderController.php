<?php

namespace App\Controller;

use App\DTO\order\CreateOrderRequest;
use App\DTO\order\InventoryOrderDetailsResponse;
use App\DTO\order\OrderDetailsResponse;
use App\DTO\order\OrderParticipantResponse;
use App\DTO\order\TableOrderResponse;
use App\Entity\GemTransaction;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderDiscount;
use App\Entity\User;
use App\Enum\BoxType;
use App\Enum\OrderState;
use App\Repository\BoxRepository;
use App\Repository\InventoryRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/order')]
class OrderController extends AbstractController
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    #[Route('/{brawlTag}', name: 'order_list', methods: ['GET'])]
    public function list(TranslatorInterface $translator, string $brawlTag): JsonResponse
    {
        if ($brawlTag === 'all') {
            $brawlTag = null;
        }
        $orders = $this->orderRepository->getAllOrders($brawlTag);

        return new JsonResponse(array_map(fn($order) => new TableOrderResponse(
            $order['id'],
            $order['invoice_number'],
            $order['purchase_date'],
            $order['state'],
            $order['username'],
            $order['user_image'],
            $order['discount'],
            $order['total_price'],
            $order['total_with_discount'],
            $translator
        ), $orders));
    }

    #[Route('/details/{orderId}', name: 'get_inventory_by_order', methods: ['GET'])]
    public function getAllOrderDetails(
        InventoryRepository $inventoryRepository,
        OrderRepository $orderRepository,
        TranslatorInterface $translator,
        int $orderId
    ): JsonResponse {
        $orderDetails = $orderRepository->getOrderDetails($orderId);
        $inventory = $inventoryRepository->getInventoryForOrderDetails($orderId);

            $ordersResponse = new OrderDetailsResponse();

            $order = $orderDetails;
            $orderResponseFrom = new OrderParticipantResponse();
            $orderResponseFrom->setUsername($order['from_username']);
            $orderResponseFrom->setName($order['from_name']);
            $orderResponseFrom->setSurname($order['from_surname']);
            $orderResponseFrom->setDni($order['from_dni']);
            $orderResponseFrom->setId($order['from_id']);

            $orderResponseTo = new OrderParticipantResponse();

            if ($order['to_id'] !== null) {
                $orderResponseTo->setUsername($order['to_username']);
                $orderResponseTo->setName($order['to_name']);
                $orderResponseTo->setSurname($order['to_surname']);
                $orderResponseTo->setDni($order['to_dni']);
                $orderResponseTo->setId($order['to_id']);
            }

            $ordersResponse->setFrom($orderResponseFrom);
            $ordersResponse->setTo($orderResponseTo);

            $ordersResponse->setSubTotal($order['sub_total']);
            $ordersResponse->setTotal($order['total']);
            $ordersResponse->setDiscount($order['discount']);
            $ordersResponse->setGems($order['gems']);
            $ordersResponse->setInvoiceNumber($order['invoice_number']);
            $ordersResponse->setPurchaseDate($order['purchase_date']);
            $ordersResponse->setState($translator->trans('OrderState.' . OrderState::tryFrom($order['state'])->name, domain: 'enums'));

            $inventoryResponse = [];
            for ($i = 0; $i < count($inventory); $i++) {
                $inventoryResponse[$i] = new InventoryOrderDetailsResponse();
                $inventoryResponse[$i]->setId($inventory[$i]['id']);
                $inventoryResponse[$i]->setCollectDate($inventory[$i]['collect_date']);
                $inventoryResponse[$i]->setPrice($inventory[$i]['price']);
                $inventoryResponse[$i]->setBoxName($inventory[$i]['box_name']);
                $inventoryResponse[$i]->setBoxType($translator->trans('BoxType.' . BoxType::tryFrom($inventory[$i]['box_type'])->name, domain: 'enums'));
            }

            $ordersResponse->setInventory($inventoryResponse);

        return new JsonResponse($ordersResponse);
    }

    #[Route('/create', name: 'create_order', methods: ['POST'])]
    public function createOrder(
        #[MapRequestPayload] CreateOrderRequest $request,
        OrderRepository $orderRepository,
        BoxRepository $boxRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $order = new Order();

        $invoiceNumber = $orderRepository->generateInvoiceNumber();

        $tries = 0;
        while ($orderRepository->findBy(['invoiceNumber' => $invoiceNumber])) {
            if ($tries > 10) {
                return new JsonResponse(['status' => 'error', 'message' => 'Error generating invoice number'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $invoiceNumber = $orderRepository->generateInvoiceNumber();
            $tries++;
        }

        $entityManager->beginTransaction();

        try {
            $order->setInvoiceNumber($invoiceNumber);
            $order->setPurchaseDate(new \DateTime());
            $order->setState(OrderState::PENDING);
            $order->setCancelled(true); // The order is cancelled by default, when user pays it will be set to false
            $order->setUser($user);

            $entityManager->persist($order);

            // Flat all box id in array
            $boxIds = array_map(fn($item) => $item->boxId, $request->items);

            // Get all boxes from database
            $boxes = $boxRepository->getAllBoxDetails($boxIds);

            $userReceiver = $request->isGift ? $userRepository->findOneBy(['username' => $request->giftUsername]) : $user;

            if ($userReceiver === null) {
                throw new \Exception('User not found');
            }

            foreach ($boxes as $box) {
                $item = array_values(array_filter($request->items, fn($item) => $item->boxId === $box['id']))[0];

                // Check if it has enough quantity in database
                if ($box['boxes_left'] != -1 && $box['boxes_left'] < $item->quantity) {
                    // Set quantity to maximum available, or if not available, remove from cart
                    $item->quantity = $box['boxes_left'];
                    if ($item->quantity === 0) {
                        continue;
                    }
                }

                // Check if box is deleted
                if ($box['deleted']) {
                    continue;
                }

                $inventory = new Inventory();
                $inventory->setPrice($box['price']);
                $inventory->setOpen(false);
                $inventory->setCollectDate(new \DateTime());
                $inventory->setBox($boxRepository->find($box['id']));
                $inventory->setOrder($order);
                $inventory->setUser($userReceiver);

                $entityManager->persist($inventory);
            }

            // Commit the transaction if everything goes well
            $entityManager->flush();

            if ($request->useGems) {
                if ($user->getGems() === 0) {
                    throw new \Exception('User has no gems');
                }

                $transaction = new GemTransaction();
                $transaction->setGems(-$user->getGems()); // Gems in negative because the user is spending them
                $transaction->setDate(new \DateTime());
                $transaction->setUser($user);

                $entityManager->persist($transaction);

                $orderDiscount = new OrderDiscount();
                $orderDiscount->setOrder($order);

                $discount = round($user->getGems() * 0.01, 2);

                $orderDiscount->setDiscount($discount);
                $orderDiscount->setTransaction($transaction);

                $entityManager->persist($orderDiscount);
                $entityManager->flush();
            }

            // Commit the transaction after everything is persisted
            $entityManager->commit();

            return new JsonResponse(['status' => 'success', 'message' => 'Order created'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // If something fails, rollback the transaction
            $entityManager->rollback();

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
