<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Order\OrderService;
use Wizaplace\User\ApiKey;

class OrderController extends Controller
{
    public function getOrdersAction(): Response
    {
        $orderService = $this->get(OrderService::class);
        $orders = $orderService->getOrders();

        return $this->render('profile/orders.html.twig', ['orders' => $orders]);
    }

    public function getOrderAction($orderId): Response
    {
        $orderId = (int) $orderId;
        $orderService = $this->get(OrderService::class);

        $order = $orderService->getOrder($orderId);

        return $this->render('profile/order.html.twig', ['order' => $order]);
    }
}
