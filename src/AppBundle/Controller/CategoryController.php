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
    public function viewAction($categoryId)
    {
        $catalogService = $this->get(CatalogService::class);
        $currentCategory = $catalogService->getCategory((int)$categoryId);
        $apiUrl = $this->getParameter("api.base_url");

        $categories = $catalogService->getCategoryTree();

        $filters = [];
        $filters['categories'] = $categoryId;
        return $this->render(
            'legacy/search/search.html.twig',
            [
                'categories' => $categories,
                'currentCategory' => $currentCategory,
                'filters' => $filters,
                'apiUrl' => $apiUrl
            ]);
    }
}
