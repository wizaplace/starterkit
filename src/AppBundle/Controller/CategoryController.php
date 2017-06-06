<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wizaplace\Catalog\CatalogService;

class CategoryController extends Controller
{
    public function searchAction($categoryId)
    {
        $catalogService = $this->get(CatalogService::class);
        $currentCategory = $catalogService->getCategory((int) $categoryId);
        $filters['categories'] = $categoryId;

        return $this->render('search/category-search.html.twig', [
            'currentCategory' => $currentCategory,
            'filters' => $filters,
        ]);
    }
}
