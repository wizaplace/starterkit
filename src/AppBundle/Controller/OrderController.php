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
    private $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getOrdersAction(): Response
    {
        $orders = $this->orderService->getOrders($this->getApiKey());

        return $this->render('profile/orders.html.twig', ['orders' => $orders]);
    }

    public function getOrderAction($orderId): Response
    {
        $orderId = (int) $orderId;

        $order = $this->orderService->getOrder($orderId, $this->getApiKey());

        return $this->render('profile/order.html.twig', ['order' => $order]);
    }

    private function getApiKey(): ApiKey
    {
        return $this->get('session')->get(\AppBundle\Controller\AuthController::API_KEY);
    }
}
