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
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use WizaplaceFrontBundle\Service\BasketService;

class CheckoutController extends Controller
{
    const SESSION_BASKET_ATTRIBUTE = '_basketId';

    /** @var TranslatorInterface */
    private $translator;

    /** @var BasketService */
    private $basketService;

    public function __construct(TranslatorInterface $translator, BasketService $basketService)
    {
        $this->translator = $translator;
        $this->basketService = $basketService;
    }

    public function loginAction(): Response
    {
        if (!is_null($this->getUser())) {
            // User is already authenticated, go to next step
            return $this->redirect($this->generateUrl('checkout_addresses'));
        }

        $basket = $this->basketService->getBasket();

        return $this->render('@App/checkout/login.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addressesAction(): Response
    {
        $basket = $this->basketService->getBasket();

        $user = $this->getUser()->getWizaplaceUser();
        // @codingStandardsIgnoreLine
        $addressesAreIdentical = $user->getBillingAddress() == $user->getShippingAddress();
        $countries = Intl::getRegionBundle()->getCountryNames();

        return $this->render('@App/checkout/addresses.html.twig', [
            'basket' => $basket,
            'addressesAreIdentical' => $addressesAreIdentical,
            'countries' => $countries,
        ]);
    }

    public function paymentAction(): Response
    {
        $basketService = $this->basketService;
        $basket = $basketService->getBasket();
        $payments = $basketService->getPayments();

        return $this->render('@App/checkout/payment.html.twig', [
            'basket' => $basket,
            'payments' => $payments,
        ]);
    }

    public function submitPaymentAction(Request $request): Response
    {
        $basketService = $this->basketService;
        $paymentId = $request->request->get('paymentId');
        $paymentInfo = $basketService->checkout(
            $paymentId,
            true,
            $this->generateUrl('checkout_complete', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $paymentRedirectUrl = $paymentInfo->getRedirectUrl();
        if ($paymentRedirectUrl) {
            return $this->redirect((string) $paymentRedirectUrl);
        }
        $htmlContent = $paymentInfo->getHtml();

        return $this->render('@App/checkout/payment-transfer-info.html.twig', [
            'htmlContent' => $htmlContent,
        ]);
    }

    public function completeAction(Request $request): Response
    {
        $success = (bool) $request->query->get("success", true);
        if (!$success) {
            $this->addFlash('error', $this->translator->trans('payment_failed'));

            return $this->redirect($this->generateUrl('checkout_payment'));
        }
        $this->basketService->forgetBasket();

        $orderIds = $request->query->get("orderIds", []);

        return $this->render('@App/checkout/confirmation.html.twig', [
            'orderIds' => $orderIds,
        ]);
    }
}
