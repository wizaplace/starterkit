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
use Wizaplace\Basket\BasketService;

class CheckoutController extends Controller
{
    const SESSION_BASKET_ATTRIBUTE = '_basketId';

    public function loginAction(): Response
    {
        $basketId = $this->getBasketId();
        $basket = $this->get(BasketService::class)->getBasket($basketId);

        return $this->render('legacy/checkout/login.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addressesAction(): Response
    {
        $basketId = $this->getBasketId();
        $basket = $this->get(BasketService::class)->getBasket($basketId);

        return $this->render('legacy/checkout/addresses.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function paymentAction(): Response
    {
        $basketService = $this->get(BasketService::class);
        $basketId = $this->getBasketId();
        $basket = $basketService->getBasket($basketId);
        $payments = $basketService->getPayments($basketId);

        return $this->render('legacy/checkout/payment.html.twig', [
            'basket' => $basket,
            'payments' => $payments,
        ]);
    }

    public function completeAction(Request $request): Response
    {
        $basketService = $this->get(BasketService::class);
        $paymentId = $request->request->get('paymentId');
        $paymentInfo = $basketService->checkout(
            $this->getBasketId(),
            $paymentId,
            true,
            $this->get('session')->get(\AppBundle\Controller\AuthController::API_KEY)
        );

        if ($paymentInfo->getRedirectUrl()) {
            return $this->redirect($paymentInfo->getRedirectUrl());
        }

        return $this->render('legacy/checkout/confirmation.html.twig', [
                'paymentInfo' => $paymentInfo,
        ]);
    }

    protected function getBasketId(): string
    {
        $basketId = $this->get('session')->get(self::SESSION_BASKET_ATTRIBUTE);

        if (null === $basketId) {
            $basketId = $this->get(BasketService::class)->create();
            $this->get('session')->set(self::SESSION_BASKET_ATTRIBUTE, $basketId);
        }

        return $basketId;
    }
}
