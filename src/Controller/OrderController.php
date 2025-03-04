<?php

namespace App\Controller;

use App\DTO\order\CreateOrderRequest;
use App\DTO\order\InventoryOrderDetailsResponse;
use App\DTO\order\OrderDetailsResponse;
use App\DTO\order\OrderParticipantResponse;
use App\DTO\order\TableOrderResponse;
use App\Entity\Box;
use App\Entity\GemTransaction;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderDiscount;
use App\Entity\User;
use App\Enum\BoxType;
use App\Enum\OrderState;
use App\Repository\BoxRepository;
use App\Repository\GemTransactionRepository;
use App\Repository\InventoryRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
    private GemTransactionRepository $gemTransactionRepository;
    private InventoryRepository $inventoryRepository;

    public function __construct(OrderRepository $orderRepository, GemTransactionRepository $gemTransactionRepository, InventoryRepository $inventoryRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->gemTransactionRepository = $gemTransactionRepository;
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
        while ($orderRepository->findBy(['invoice_number' => $invoiceNumber])) {
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

            if ($request->isGift && $request->giftUsername === null || $request->isGift && $request->giftUsername === $user->getUsername()) {
                throw new \Exception('Gift username is not valid');
            }

            $userReceiver = $request->isGift ? $userRepository->findOneBy(['username' => $request->giftUsername]) : $user;

            if ($userReceiver === null) {
                throw new \Exception('User not found');
            }

            $total = 0;
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

                if ($item->quantity > 1000) {
                    throw new \Exception('Max quantity per box is 1000');
                }

                for ($i = 0; $i < $item->quantity; $i++) {
                    $inventory = new Inventory();
                    $inventory->setPrice($box['price']);
                    $inventory->setOpen(false);
                    $inventory->setCollectDate(new \DateTime());
                    $inventory->setBox($boxRepository->find($box['id']));
                    $inventory->setOrder($order);
                    $inventory->setUser($userReceiver);

                    $entityManager->persist($inventory);
                }

                $total += $box['price'] * $item->quantity;
            }

            // Commit the transaction if everything goes well
            $entityManager->flush();

            if ($request->useGems) {
                if ($user->getGems() === 0) {
                    throw new \Exception('User has no gems');
                }

                $transactionId = $this->gemTransactionRepository->addGemTransaction($user->getId(), -$user->getGems());
                $transaction = $entityManager->getRepository(GemTransaction::class)->find($transactionId);

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

            $message = $order->getId();

            if ($total == 0) {
                $message .= '//skipPayment';
            }

            return new JsonResponse(['status' => 'success', 'message' => $message], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // If something fails, rollback the transaction
            $entityManager->rollback();

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage(), 'temp' => $e->getLine(), 'file' => $e->getTrace()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/pay/{orderId}', name: 'pay_order', methods: ['POST'])]
    public function payOrder(
        #[MapEntity(Order::class, id: 'orderId')]
        Order $order,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $entityManager->beginTransaction();

        try {
            if (!$order->isCancelled() || $order->getState() !== OrderState::PENDING) {
                return new JsonResponse(['status' => 'error', 'message' => 'Order cannot be paid'], Response::HTTP_BAD_REQUEST);
            }

            /** @var User $user */
            $user = $this->getUser();
            $orderDetails = $this->orderRepository->getOrderDetails($order->getId(), false);

            $cartTotal = round($orderDetails['total'], 2);
            $subtotal = round(($cartTotal / 1.21) - $orderDetails['discount'], 2);
            $iva = round($subtotal * 0.21, 2);
            $total = $subtotal + $iva;

            // Calculate the gems that the user will receive (10% of the total price)
            $gems = round($total * 10);
            $this->gemTransactionRepository->addGemTransaction($user->getId(), $gems);

            $order->setCancelled(false);
            $order->setState(OrderState::PAID);

            $orderItems = $this->inventoryRepository->getInventoryForOrderDetails($order->getId());

            // Group the items by box id
            $groupedItems = [];

            foreach ($orderItems as $item) {
                if (!isset($groupedItems[$item['id']])) {
                    $groupedItems[$item['id']] = 0;
                }

                $groupedItems[$item['id']]++;
            }

            // Update the boxes left on table box
            foreach ($groupedItems as $boxId => $quantity) {
                $box = $entityManager->getRepository(Box::class)->find($boxId);

                // Only update if the box has a limit
                if ($box->getQuantity() !== -1) {
                    $box->setQuantity($box->getQuantity() - $quantity);
                    $entityManager->persist($box);
                }
            }

            $entityManager->persist($order);
            $entityManager->flush();
            $entityManager->commit();

            return new JsonResponse(['status' => 'success', 'message' => $gems], Response::HTTP_OK);
        } catch (\Exception $e) {
            $entityManager->rollback();
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
