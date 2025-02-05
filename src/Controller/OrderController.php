<?php

namespace App\Controller;

use App\DTO\order\TableOrderResponse;
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
}
