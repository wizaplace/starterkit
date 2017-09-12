<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\SDK\Basket\BasketService;
use Wizaplace\SDK\Basket\Exception\CouponAlreadyPresent;
use Wizaplace\SDK\Basket\Exception\CouponNotInTheBasket;

class BasketController extends Controller
{
    public const SESSION_BASKET_ATTRIBUTE = '_basketId';

    /** @var BasketService */
    private $basketService;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(BasketService $basketService, TranslatorInterface $translator)
    {
        $this->basketService = $basketService;
        $this->translator = $translator;
    }

    public function basketAction(): Response
    {
        $basketId = $this->getBasketId();
        $basket = $this->basketService->getBasket($basketId);

        return $this->render('@App/checkout/basket.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addProductAction(Request $request): JsonResponse
    {
        $basketId = $this->getBasketId();
        $declinationId = $request->request->get('declinationId');
        $requestedQuantity = (int) $request->request->get('quantity');

        $addedQuantity = $this->basketService->addProductToBasket($basketId, $declinationId, $requestedQuantity);

        // warning message regarding stock
        $notEnoughStockMessage = $this->translator->trans('not_enough_stock');
        $message = ($addedQuantity < $requestedQuantity) ? $notEnoughStockMessage : null;

        return new JsonResponse([
            'message' => $message,
        ]);
    }

    public function removeProductAction(Request $request): Response
    {
        // redirection url
        $referer = $request->headers->get('referer');

        $basketId = $this->getBasketId();
        $declinationId = $request->get('declinationId');

        $this->basketService->removeProductFromBasket($basketId, $declinationId);

        // add a success message
        $message = $this->translator->trans('product_deleted_from_basket');
        $this->addFlash('success', $message);

        return $this->redirect($referer);
    }

    public function cleanBasketAction(Request $request): Response
    {
        $token = $request->request->get("token");
        if ($this->isCsrfTokenValid("clean", $token)) {
            $this->get("session")->remove(self::SESSION_BASKET_ATTRIBUTE);
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function updateProductQuantityAction(Request $request): JsonResponse
    {
        $basketId = $this->getBasketId();
        $declinationId = $request->request->get('declinationId');
        $quantity = $request->request->getInt('quantity');

        // remove product from basket if quantity is 0
        if ($quantity === 0) {
            $this->basketService->removeProductFromBasket($basketId, $declinationId);

            $message = $this->translator->trans('product_deleted_from_basket');
            $this->addFlash('success', $message);

            return new JsonResponse();
        }

        $realQuantity = $this->basketService->updateProductQuantity($basketId, $declinationId, $quantity);

        return new JsonResponse([
            'realQuantity' => $realQuantity,
        ]);
    }

    public function addCouponAction(Request $request): Response
    {
        $basketId = $this->getBasketId();
        $coupon = $request->request->get('coupon');

        try {
            $this->basketService->addCoupon($basketId, $coupon);
        } catch (CouponAlreadyPresent $e) {
            //Si le coupon est déjà dans le panier, on fait comme si tout s'etait bien passé
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function removeCouponAction(Request $request): Response
    {
        $basketId = $this->getBasketId();
        $coupon = $request->request->get('coupon');

        try {
            $this->basketService->addCoupon($basketId, $coupon);
        } catch (CouponNotInTheBasket $e) {
            //Si le coupon n'est pas dans le panier, on est dans l'état final attendu
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function selectShippingsAction(Request $request): JsonResponse
    {
        $basketId = $this->getBasketId();

        $shippingGroupId = $request->get('shippingGroupId');
        $shippingId = $request->get('shippingId');

        $shippings[$shippingGroupId] = $shippingId;
        $this->basketService->selectShippings($basketId, $shippings);

        $message = $this->translator->trans('shipping_method_updated');

        return new JsonResponse([
            'message' => $message,
        ]);
    }

    protected function getBasketId(): string
    {
        $basketId = $this->get('session')->get(self::SESSION_BASKET_ATTRIBUTE);

        if (null === $basketId) {
            $basketId = $this->basketService->create();
            $this->get('session')->set(self::SESSION_BASKET_ATTRIBUTE, $basketId);
        }

        return $basketId;
    }
}
