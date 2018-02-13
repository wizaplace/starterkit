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
use Wizaplace\SDK\Order\OrderService;
use Wizaplace\SDK\User\UserService;
use WizaplaceFrontBundle\Controller\ProfileController as BaseController;
use WizaplaceFrontBundle\Service\InvoiceService;

class ProfileController extends BaseController
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    public function __construct(TranslatorInterface $translator, UserService $userService, InvoiceService $invoiceService)
    {
        parent::__construct($translator, $userService);
        $this->invoiceService = $invoiceService;
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
        $order = $this->get(OrderService::class)->getOrder($orderId);

        return $this->render('@App/profile/order.html.twig', [
            "order" => $order,
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
