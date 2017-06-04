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

class SearchController extends Controller
{
    public function quickSearchAction(): Response
    {
        $apiUrl = $this->getParameter("api.base_url");

        return $this->render('legacy/search/search.html.twig', [
            'apiUrl' => $apiUrl
        ]);
    }

    public function apiSearchAction(Request $request): JsonResponse
    {
        $apiBaseUrl = $this->getParameter('api.base_url');
        $httpClient = $this->get('http.client');

        $query = $request->query;
        $filters = $query->get('filters', []);
        $filters['companies'] = $this->get('kernel')->getVendorId();
        $query->add(['filters' => $filters]);

        $response = $httpClient->get($apiBaseUrl . '/catalog/search/products', [
            'query' => $query->all(),
        ]);

        return new JsonResponse(json_decode($response->getBody()->getContents(), true));
    }
}
