<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Catalog\CatalogService;

class HomeController extends Controller
{
    public function homeAction(CatalogService $catalogService): Response
    {
        // latest products
        $latestProducts = $catalogService->search('', [], ['timestamp' => 'desc'], 6)->getProducts();

        return $this->render('home/home.html.twig', [
            'latestProducts' => $latestProducts,
        ]);
    }
}
