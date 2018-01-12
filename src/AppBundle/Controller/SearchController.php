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
use Wizaplace\SDK\Catalog\CatalogService;
use WizaplaceFrontBundle\Service\FavoriteService;

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

        // gather user's favorites
        $userFavoriteIds = [];

        if($this->getUser()) {
            $favoriteService = $this->get(FavoriteService::class);
            $favoriteProducts = $favoriteService->getAll();

            $userFavoriteIds = array_map(function ($product) {
                return (string)$product->getId();
            }, $favoriteProducts);
        }

        return $this->render('@App/search/search.html.twig', [
            'searchQuery' => $request->query->get('q'),
            'filters' => $filters,
            'selectedCategory' => $selectedCategory,
            'userFavoriteIds' => $userFavoriteIds,
        ]);
    }
}
