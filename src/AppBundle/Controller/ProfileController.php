<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use WizaplaceFrontBundle\Controller\ProfileController as BaseController;
use WizaplaceFrontBundle\Service\InvoiceService;
use Wizaplace\SDK\Order\OrderService;
use Wizaplace\SDK\Shipping\MondialRelayService;
use Wizaplace\SDK\User\UserService;

class ProfileController extends BaseController
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var MondialRelayService
     */
    private $mondialRelayService;

    public function __construct(TranslatorInterface $translator, UserService $userService, InvoiceService $invoiceService, OrderService $orderService, MondialRelayService $mondialRelayService)
    {
        parent::__construct($translator, $userService);
        $this->invoiceService = $invoiceService;
        $this->orderService = $orderService;
        $this->mondialRelayService = $mondialRelayService;
    }

    public function viewAction(): Response
    {
        return parent::viewAction();
    }

    public function addressesAction(): Response
    {
        return parent::addressesAction();
    }

    public function orderAction(int $orderId): Response
    {
        $order = $this->orderService->getOrder($orderId);
        $pickupPoint = $order->getShippingAddress()->getPickupPointId()
            ? $this->mondialRelayService->getPickupPoint($order->getShippingAddress()->getPickupPointId())
            : null;

        return $this->render('@App/profile/order.html.twig', [
            'order' => $order,
            'pickupPoint' => $pickupPoint,
        ]);
    }

    public function ordersAction(): Response
    {
        return parent::ordersAction();
    }

    public function returnsAction(): Response
    {
        return parent::returnsAction();
    }

    public function returnAction(int $orderReturnId): Response
    {
        return parent::returnAction($orderReturnId);
    }

    public function createOrderReturnAction(Request $request)
    {
        return parent::createOrderReturnAction($request);
    }

    public function afterSalesServiceAction(Request $request): Response
    {
        return parent::afterSalesServiceAction($request);
    }

    public function favoritesAction(): Response
    {
        return parent::favoritesAction();
    }

    public function updateProfileAction(Request $request)
    {
        return parent::updateProfileAction($request);
    }

    public function newsletterAction(): Response
    {
        return parent::newsletterAction();
    }

    public function downloadPdfInvoiceAction(int $orderId): Response
    {
        return $this->invoiceService->downloadPdf($orderId);
    }
}
