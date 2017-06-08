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
use Wizaplace\Order\Order;
use Wizaplace\Order\OrderService;
use Wizaplace\User\ApiKey;

class OrderController extends Controller
{
    public function getOrdersAction(OrderService $orderService): Response
    {
        $orders = $orderService->getOrders($this->getApiKey());

        return $this->render('profile/orders.html.twig', ['orders' => $orders]);
    }

    public function getOrderAction($orderId, OrderService $orderService): Response
    {
        $orderId = (int) $orderId;

        $order = $orderService->getOrder($orderId, $this->getApiKey());

        return $this->render('profile/order.html.twig', ['order' => $order]);
    }

    private function getApiKey(): ApiKey
    {
        return $this->get('session')->get(\AppBundle\Controller\AuthController::API_KEY);
    }
}
