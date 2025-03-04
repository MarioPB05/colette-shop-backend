<?php

namespace App\Controller;

use App\DTO\order\InventoryOrderDetailsResponse;
use App\DTO\order\OrderDetailsResponse;
use App\DTO\order\OrderParticipantResponse;
use App\DTO\order\TableOrderResponse;
use App\Enum\BoxType;
use App\Enum\OrderState;
use App\Repository\InventoryRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

            if ($order['to_id'] !== $order['from_id']) {
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

}
