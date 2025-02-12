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

    #[Route('/details/{order_id}', name: 'get_inventory_by_order', methods: ['GET'])]
    public function getAllOrderDetails(
        InventoryRepository $inventoryRepository,
        OrderRepository $orderRepository,
        TranslatorInterface $translator,
        int $order_id
    ): JsonResponse {
        $orderDetails = $orderRepository->getOrderDetails($order_id);
        $inventory = $inventoryRepository->getInventoryForOrderDetails($order_id);

        $orderResponseList = [];

        for ($i = 0; $i < count($orderDetails); $i++) {
            $ordersResponse = new OrderDetailsResponse();

            $order = $orderDetails[$i];
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
            for ($j = 0; $j < count($inventory); $j++) {
                $inventoryResponse[$j] = new InventoryOrderDetailsResponse();
                $inventoryResponse[$j]->setId($inventory[$j]['id']);
                $inventoryResponse[$j]->setCollectDate($inventory[$j]['collect_date']);
                $inventoryResponse[$j]->setPrice($inventory[$j]['price']);
                $inventoryResponse[$j]->setBoxName($inventory[$j]['box_name']);
                $inventoryResponse[$j]->setBoxType($translator->trans('BoxType.' . BoxType::tryFrom($inventory[$j]['box_type'])->name, domain: 'enums'));
                $inventoryResponse[$j]->setOpenDate($inventory[$j]['open_date']);
            }

            $ordersResponse->setInventory($inventoryResponse);

            $orderResponseList[] = $ordersResponse;

        }

        return new JsonResponse($orderResponseList);
    }

}
