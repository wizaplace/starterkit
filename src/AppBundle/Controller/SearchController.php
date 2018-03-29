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
use Wizaplace\SDK\Catalog\CatalogServiceInterface;
use Wizaplace\SDK\Catalog\GeoFilter;
use WizaplaceFrontBundle\Service\FavoriteService;
use WizaplaceFrontBundle\Service\JsonSearchService;

class SearchController extends Controller
{
    /**
     * @var CatalogServiceInterface
     */
    private $catalogService;

    /**
     * @var JsonSearchService
     */
    private $jsonSearchService;

    public function __construct(CatalogServiceInterface $catalogService, JsonSearchService $jsonSearchService)
    {
        $this->catalogService = $catalogService;
        $this->jsonSearchService = $jsonSearchService;
    }

    public function searchAction(Request $request): Response
    {
        $selectedCategoryId = $request->query->getInt("selected_category_id");
        $selectedCategory = $selectedCategoryId ? $this->catalogService->getCategory($selectedCategoryId) : null;

        $filters = [];
        if ($selectedCategoryId) {
            $filters['categories'] = $selectedCategoryId;
        }

        $userFavoriteIds = $this->get(FavoriteService::class)->getFavoriteIds();

        return $this->render('@App/search/search.html.twig', [
            'searchQuery' => $request->query->get('q'),
            'filters' => $filters,
            'selectedCategory' => $selectedCategory,
            'userFavoriteIds' => $userFavoriteIds,
        ]);
    }

    public function jsonSearchAction(Request $request): JsonResponse
    {
        $query = $request->query->get('query', '');
        $filters = $request->query->get('filters', []);
        $sorting = $request->query->get('sorting', []);
        $resultsPerPage = $request->query->getInt('resultsPerPage', 12);
        $page = $request->query->getInt('page', 1);
        $geoFilter = null;
        if ($request->query->has('geo')) {
            $geo = $request->query->get('geo');
            $geoFilter = new GeoFilter($geo['lat'], $geo['lng'], $geo['radius'] ?? null);
        }

        return new JsonResponse($this->jsonSearchService->search($query, $filters, $sorting, $resultsPerPage, $page, $geoFilter), Response::HTTP_OK, [], true);
    }
}
