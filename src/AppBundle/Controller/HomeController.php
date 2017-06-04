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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wizaplace\Basket\Basket;
use Wizaplace\Basket\BasketService;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Catalog\ProductSummary;

class HomeController extends Controller
{
    const SESSION_BASKET_ATTRIBUTE = '_basketId';

    public function viewAction(): Response
    {
        $catalogService = $this->get(CatalogService::class);

        $categories = $catalogService->getCategoryTree(); // decode the JSON into an associative array

        //get basket
        $basket = $this->getBasket();

        // latestProducts
        $latestProducts = $catalogService->search('',[],['timestamp'=> 'desc'], 6)->getProducts();

        return $this->render('legacy/home/home.html.twig', [
            'categories' => $categories,
            'latestProducts' => $latestProducts,
            'basket' => $basket
        ]);
    }

    protected function getBasket(): Basket
    {
        $basketId = $this->get('session')->get(self::SESSION_BASKET_ATTRIBUTE);

        if (null === $basketId) {
            $basketId = $this->get(BasketService::class)->create();
            $this->get('session')->set(self::SESSION_BASKET_ATTRIBUTE, $basketId);
        }

        try {
            return $this->get(BasketService::class)->getBasket($basketId);
        } catch (\Exception $e) { //Si l'id actuel ne marche pas, on tente d'en créer un nouveau.
            $basketId = $this->get(BasketService::class)->create();
            $this->get('session')->set(self::SESSION_BASKET_ATTRIBUTE, $basketId);

            return $this->get(BasketService::class)->getBasket($basketId);
        }
    }
}
