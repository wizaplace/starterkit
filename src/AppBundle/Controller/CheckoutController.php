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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\Basket\BasketService;

class CheckoutController extends Controller
{
    const SESSION_BASKET_ATTRIBUTE = '_basketId';

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function loginAction(): Response
    {
        if (!is_null($this->getUser())) {
            // User is already authenticated, go to next step
            return $this->redirect($this->generateUrl('checkout_addresses'));
        }

        $basketId = $this->getBasketId();
        $basket = $this->get(BasketService::class)->getBasket($basketId);

        return $this->render('checkout/login.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addressesAction(): Response
    {
        $basketId = $this->getBasketId();
        $basket = $this->get(BasketService::class)->getBasket($basketId);

        return $this->render('checkout/addresses.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function paymentAction(): Response
    {
        $basketService = $this->get(BasketService::class);
        $basketId = $this->getBasketId();
        $basket = $basketService->getBasket($basketId);
        $payments = $basketService->getPayments($basketId);

        return $this->render('checkout/payment.html.twig', [
            'basket' => $basket,
            'payments' => $payments,
        ]);
    }

    public function submitPaymentAction(Request $request): Response
    {
        $basketService = $this->get(BasketService::class);
        $paymentId = $request->request->get('paymentId');
        $paymentInfo = $basketService->checkout(
            $this->getBasketId(),
            $paymentId,
            true,
            $this->generateUrl('checkout_complete', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $paymentRedirectUrl = $paymentInfo->getRedirectUrl();
        if ($paymentRedirectUrl) {
            return $this->redirect($paymentRedirectUrl);
        }

        // @FIXME : display $paymentInfo->getHtml()
        return $this->redirect($this->generateUrl('checkout_complete'));
    }

    public function completeAction(Request $request): Response
    {
        $success = (bool) $request->query->get("success", true);
        if (!$success) {
            $this->addFlash('error', $this->translator->trans('payment_failed'));

            return $this->redirect($this->generateUrl('checkout_payment'));
        }

        $orderIds = $request->query->get("orderIds", []);

        return $this->render('checkout/confirmation.html.twig', [
            'orderIds' => $orderIds,
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
