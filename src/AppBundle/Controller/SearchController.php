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
use Wizaplace\Catalog\CatalogService;

class SearchController extends Controller
{
    public function searchAction(Request $request): Response
    {
        $catalogService = $this->get(CatalogService::class);
        $selectedCategoryId = (int) $request->get("selected_category_id");
        $selectedCategory = $selectedCategoryId ? $catalogService->getCategory($selectedCategoryId) : null;

        $filters = [];
        if ($selectedCategoryId) {
            $filters['categories'] = $selectedCategoryId;
        }

        return $this->render('search/search.html.twig', [
            'searchQuery' => $request->query->get('q'),
            'filters' => $filters,
            'selectedCategory' => $selectedCategory,
        ]);
    }
}
