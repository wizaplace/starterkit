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
use Wizaplace\SDK\Basket\SetPickupPointCommand;
use Wizaplace\SDK\Shipping\MondialRelayService;
use Wizaplace\SDK\User\UserTitle;

class CheckoutController extends Controller
{
    const SESSION_BASKET_ATTRIBUTE = '_basketId';

    /** @var TranslatorInterface */
    private $translator;

    /** @var BasketService */
    private $basketService;

    /** @var MondialRelayService */
    private $mondialRelayService;

    public function __construct(TranslatorInterface $translator, BasketService $basketService, MondialRelayService $mondialRelayService)
    {
        $this->translator = $translator;
        $this->basketService = $basketService;
        $this->mondialRelayService = $mondialRelayService;
    }

    public function loginAction(): Response
    {
        $basket = $this->basketService->getBasket();

        // User is already authenticated, go to next step
        if (!is_null($this->getUser())) {
            if ($basket->isPickupPointsShipping()) {
                return $this->redirect($this->generateUrl('checkout_pickup_points'));
            }

            return $this->redirect($this->generateUrl('checkout_addresses'));
        }

        return $this->render('@App/checkout/login.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addressesAction(): Response
    {
        $basket = $this->basketService->getBasket();

        if ($basket->isPickupPointsShipping()) {
            return $this->redirect($this->generateUrl('checkout_pickup_points'));
        }

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

    /**
     * This is the page that lets you pick a relay point.
     * You can also use the official Mondial Relay Javascript SDK, which
     * displays a map to the user. In this case, you will have to request
     * the Brand Code (N° Enseigne) to the API:
     *
     * ```
     *     $brandCode = $this->mondialRelayService->getBrandCode();
     * ```
     */
    public function pickupPointsAction(Request $request): Response
    {
        $basket = $this->basketService->getBasket();

        if ($request->request->has('pickupPointId')) {
            $command = (new SetPickupPointCommand())
                ->setBasketId($basket->getId())
                ->setPickupPointId($request->request->get('pickupPointId'))
                ->setTitle(new UserTitle($request->request->get('title')))
                ->setFirstName($request->request->get('firstName'))
                ->setLastName($request->request->get('lastName'))
            ;

            $res = $this->basketService->setMondialRelayPickupPoint($command);

            return $this->redirect($this->generateUrl('checkout_payment'));
        }

        $user = $this->getUser()->getWizaplaceUser();
        // @codingStandardsIgnoreLine
        $addressesAreIdentical = $user->getBillingAddress() == $user->getShippingAddress();
        $countries = Intl::getRegionBundle()->getCountryNames();

        $zipCode = $request->query->get('zipCode');
        $results = null;
        if ($zipCode) {
            $results = $this->mondialRelayService->searchPickupPoints($zipCode);
        }

        return $this->render('@App/checkout/pickup-points.html.twig', [
            'basket' => $basket,
            'countries' => $countries,
            'results' => $results,
            'zipCode' => $zipCode,
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
