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
use Wizaplace\Basket\Basket;
use Wizaplace\Catalog\CatalogService;

class ProductController extends Controller
{
    public function viewAction($productId) : Response
    {
        $product = $this->get(CatalogService::class)->getProductById($productId);

        // latestProducts
        $catalogService = $this->get(CatalogService::class);
        $latestProducts = $catalogService->search('', [], ['timestamp' => 'desc'], 6)->getProducts();

        return $this->render('product/product.html.twig', [
            'product' => $product,
            'latestProducts' => $latestProducts,
        ]);
    }
}
