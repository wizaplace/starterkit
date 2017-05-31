<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Order\OrderService;
use Wizaplace\User\ApiKey;

class OrderController extends Controller
{
    public function getOrdersAction(): Response
    {
        $orderService = $this->get(OrderService::class);
        $orders = $orderService->getOrders($this->getApiKey());

        return $this->render('legacy/profile/orders.html.twig', ['orders' => $orders]);
    }

    public function getOrderAction($orderId): Response
    {
        $orderId = (int) $orderId;
        $orderService = $this->get(OrderService::class);

        $order = $orderService->getOrder($orderId, $this->getApiKey());

        return $this->render('legacy/profile/order.html.twig', ['order' => $order]);
    }

    private function getApiKey(): ApiKey
    {
        return $this->get('session')->get(\AppBundle\Controller\AuthController::API_KEY);
    }
}
