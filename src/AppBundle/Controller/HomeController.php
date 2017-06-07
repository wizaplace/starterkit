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
    private $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function homeAction(): Response
    {
        // latest products
        $latestProducts = $this->catalogService->search('', [], ['timestamp' => 'desc'], 6)->getProducts();

        return $this->render('home/home.html.twig', [
            'latestProducts' => $latestProducts,
        ]);
    }
}
