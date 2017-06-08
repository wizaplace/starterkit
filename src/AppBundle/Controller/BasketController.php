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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wizaplace\Basket\BasketService;
use Wizaplace\Basket\Exception\CouponAlreadyPresent;
use Wizaplace\Basket\Exception\CouponNotInTheBasket;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Image\ImageService;

class BasketController extends Controller
{
    const SESSION_BASKET_ATTRIBUTE = '_basketId';

    public function basketAction(BasketService $basketService): Response
    {
        $basketId = $this->getBasketId();
        $basket = $basketService->getBasket($basketId);

        return $this->render('checkout/basket.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addProductAction(
        Request $request,
        BasketService $basketService,
        CatalogService $catalogService,
        ImageService $imageService
    ): JsonResponse {
        $basketId = $this->getBasketId();
        $declinationId = $request->request->get('declinationId');
        $requestedQuantity = $request->request->get('quantity');
        $product = $catalogService->getProductById($declinationId);

        //  get product data
        $addedProduct["name"] = $product->getName();
        $addedProduct["price"] = $product->getDeclinations()["0"]->getPrice();
        $addedProduct["quantity"] = $basketService->addProductToBasket($basketId, $declinationId, (int) $requestedQuantity);

        // get product main image
        $productImages = $product->getDeclinations()["0"]->getImages();
        if (count($productImages)) {
            $imageId = reset($productImages)->getId();
            $addedProduct["imageLink"] = $imageService->getImageLink($imageId, 100, 100);
        }

        // warning message regarding stock
        $notEnoughStockMessage = "Le nombre de produits en stock est insuffisant pour votre commande.";
        $message = ($addedProduct["quantity"] < $requestedQuantity) ? $notEnoughStockMessage : null;

        return new JsonResponse([
            "addedProduct" => $addedProduct,
            "message" => $message,
        ]);
    }

    public function removeProductAction(Request $request, BasketService $basketService): Response
    {
        $basketId = $this->getBasketId();
        $declinationId = $request->request->get('declinationId');

        $basketService->removeProductFromBasket($basketId, $declinationId);
        $basket = $basketService->getBasket($basketId);

        return $this->render('checkout/basket.html.twig', [
            'basket' => $basket,
        ]);
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

    public function updateProductQuantityAction(Request $request, BasketService $basketService): Response
    {
        $basketId = $this->getBasketId();
        $declinationId = $request->request->get('declinationId');
        $quantity = $request->request->get('quantity');
        $realQuantity = $basketService->updateProductQuantity($basketId, $declinationId, (int) $quantity);

        $basketId = $this->getBasketId();
        $basket = $basketService->getBasket($basketId);

        return $this->render('checkout/basket.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addCouponAction(Request $request, BasketService $basketService): Response
    {
        $basketId = $this->getBasketId();
        $coupon = $request->request->get('coupon');

        try {
            $basketService->addCoupon($basketId, $coupon);
        } catch (CouponAlreadyPresent $e) {
            //Si le coupon est déjà dans le panier, on fait comme si tout s'etait bien passé
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function removeCouponAction(Request $request, BasketService $basketService): Response
    {
        $basketId = $this->getBasketId();
        $coupon = $request->request->get('coupon');

        try {
            $basketService->addCoupon($basketId, $coupon);
        } catch (CouponNotInTheBasket $e) {
            //Si le coupon n'est pas dans le panier, on est dans l'état final attendu
        }
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
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
