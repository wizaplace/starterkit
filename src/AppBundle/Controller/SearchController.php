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
use Wizaplace\Catalog\CatalogService;

class SearchController extends Controller
{
    public function quickSearchAction(Request $request): Response
    {
        $catalogService = $this->get(CatalogService::class);
        $categoryId = $request->get('categories');

        if (!empty($categoryId)) {
            // add searched category into a filters array
            $filters['categories'] = $categoryId;

            // get searched category
            $currentCategory = $catalogService->getCategory((int) $categoryId);
        }

        return $this->render('search/quick-search.html.twig', [
            'currentCategory' => $currentCategory ?? null,
            'filters' => $filters ?? [],
            'searchQuery' => $request->query->get('query'),
        ]);
    }

    public function apiSearchAction(Request $request): JsonResponse
    {
        $apiBaseUrl = $this->getParameter('api.base_url');
        $httpClient = $this->get('http.client');

        $response = $httpClient->get($apiBaseUrl.'catalog/search/products', [
            'query' => $request->getQueryString(),
        ]);

        return new JsonResponse(json_decode($response->getBody()->getContents(), true));
    }
}
