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
use Wizaplace\SDK\Basket\BasketComment;
use Wizaplace\SDK\Basket\Exception\CouponAlreadyPresent;
use Wizaplace\SDK\Basket\Exception\CouponNotInTheBasket;
use Wizaplace\SDK\Basket\ProductComment;
use Wizaplace\SDK\Catalog\DeclinationId;
use WizaplaceFrontBundle\Service\BasketService;

class BasketController extends Controller
{
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
        $basket = $this->basketService->getBasket();

        return $this->render('@App/checkout/basket.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addProductAction(Request $request): JsonResponse
    {
        $declinationId = new DeclinationId($request->request->get('declinationId'));
        $requestedQuantity = (int) $request->request->get('quantity');

        $addedQuantity = $this->basketService->addProductToBasket($declinationId, $requestedQuantity);

        // warning message regarding stock
        $notEnoughStockMessage = $this->translator->trans('not_enough_stock');
        $message = ($addedQuantity < $requestedQuantity) ? $notEnoughStockMessage : null;

        return new JsonResponse([
            'message' => $message,
        ]);
    }

    public function removeItemAction(Request $request): Response
    {
        // redirection url
        $referer = $request->headers->get('referer');

        $declinationId = new DeclinationId($request->get('declinationId'));

        $this->basketService->removeProductFromBasket($declinationId);

        // add a success message
        $message = $this->translator->trans('basket.notification.success.item_deleted');
        $this->addFlash('success', $message);

        return $this->redirect($referer);
    }

    public function cleanBasketAction(Request $request): Response
    {
        $token = $request->request->get("token");
        if ($this->isCsrfTokenValid("clean", $token)) {
            $this->basketService->cleanBasket();
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function updateProductQuantityAction(Request $request): JsonResponse
    {
        $declinationId = new DeclinationId($request->request->get('declinationId'));
        $quantity = $request->request->getInt('quantity');

        // remove product from basket if quantity is 0
        if ($quantity === 0) {
            $this->basketService->removeProductFromBasket($declinationId);

            $message = $this->translator->trans('basket.notification.success.item_deleted');
            $this->addFlash('success', $message);

            return new JsonResponse();
        }

        $realQuantity = $this->basketService->updateProductQuantity($declinationId, $quantity);

        // add a notification if not enough stock
        if ($quantity > $realQuantity) {
            $message = $this->translator->trans('basket.notification.warning.not_enough_stock');
            $this->addFlash('warning', $message);
        }

        return new JsonResponse([
            'realQuantity' => $realQuantity,
        ]);
    }

    public function addCouponAction(Request $request): Response
    {
        $coupon = $request->request->get('coupon');

        try {
            $this->basketService->addCoupon($coupon);
        } catch (CouponAlreadyPresent $e) {
            //Si le coupon est déjà dans le panier, on fait comme si tout s'etait bien passé
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function removeCouponAction(Request $request): Response
    {
        $coupon = $request->request->get('coupon');

        try {
            $this->basketService->removeCoupon($coupon);
        } catch (CouponNotInTheBasket $e) {
            //Si le coupon n'est pas dans le panier, on est dans l'état final attendu
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function selectShippingsAction(Request $request): JsonResponse
    {
        $shippingGroupId = $request->get('shippingGroupId');
        $shippingId = $request->get('shippingId');

        $shippings[$shippingGroupId] = $shippingId;
        $this->basketService->selectShippings($shippings);

        $message = $this->translator->trans('shipping_method_updated');

        return new JsonResponse([
            'message' => $message,
        ]);
    }

    public function updateCommentsAction(Request $request): JsonResponse
    {
        $comment = $request->get('comment');
        $declinationId = new DeclinationId($request->get('declinationId'));

        $comments = [];

        // if $declinationId is not empty or null, it means $comment is a productComment
        if ($declinationId) {
            $comments[] = new ProductComment($declinationId, $comment);
        // else if there is only $comment, it means it is a basketComment
        } else {
            $comments[] = new BasketComment($comment);
        }

        if (!empty($comments)) {
            $this->basketService->updateComments($comments);
        }

        return new JsonResponse();
    }
}
