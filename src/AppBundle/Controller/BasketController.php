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
use Wizaplace\Basket\BasketService;
use Wizaplace\Basket\Exception\CouponAlreadyPresent;
use Wizaplace\Basket\Exception\CouponNotInTheBasket;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Image\ImageService;

class BasketController extends Controller
{
    public const SESSION_BASKET_ATTRIBUTE = '_basketId';

    /** @var BasketService */
    private $basketService;

    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    public function basketAction(): Response
    {
        $basketId = $this->getBasketId();
        $basket = $this->basketService->getBasket($basketId);

        return $this->render('checkout/basket.html.twig', [
            'basket' => $basket,
        ]);
    }

    public function addProductAction(Request $request): JsonResponse
    {
        $basketId = $this->getBasketId();
        $declinationId = $request->request->get('declinationId');
        $requestedQuantity = $request->request->get('quantity');
        $product = $this->get(CatalogService::class)->getProductById($declinationId);

        //  get product data
        $addedProduct["name"] = $product->getName();
        $addedProduct["price"] = $product->getDeclinations()["0"]->getPrice();
        $addedProduct["quantity"] = $this->basketService->addProductToBasket($basketId, $declinationId, (int) $requestedQuantity);

        // get product main image
        $productImages = $product->getDeclinations()["0"]->getImages();
        if (count($productImages)) {
            $imageId = reset($productImages)->getId();
            $imageService = $this->get(ImageService::class);
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

    public function removeProductAction(Request $request): Response
    {
        // redirection url
        $referer = $request->headers->get('referer');

        $basketId = $this->getBasketId();
        $declinationId = $request->get('declinationId');

        $this->basketService->removeProductFromBasket($basketId, $declinationId);

        // add a success message
        $this->addFlash('success', 'Le produit a bien été supprimé de votre panier.');

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
        $quantity = $request->request->get('quantity');

        // remove product from basket if quantity is 0
        if($quantity == 0) {
            $this->basketService->removeProductFromBasket($basketId, $declinationId);

            $this->addFlash("success", "Le produit a bien été supprimé de votre panier.");
            return new JsonResponse();
        }

        $realQuantity = $this->basketService->updateProductQuantity($basketId, $declinationId, (int) $quantity);

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
    
    public function selectShippingsAction(Request $request): Response
    {
        $basketId = $this->getBasketId();

        $shippingGroupId = $request->get('shippingGroupId');
        $shippingId = $request->get('shippingId');

        $shippings[$shippingGroupId] = $shippingId;
        $this->basketService->selectShippings($basketId, $shippings);

        return new JsonResponse([
            'message' => "Mode de livraison mis à jour",
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
