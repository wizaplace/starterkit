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

class SearchController extends Controller
{
    public function searchAction()
    {
        $apiUrl = $this->getParameter("api.base_url");

        return $this->render('legacy/search/search.html.twig', [
            'apiUrl' => $apiUrl
        ]);
    }

    public function apiSearchAction(Request $request) : JsonResponse
    {
        $apiBaseUrl = $this->getParameter('api.base_url');
        $httpClient = $this->get('http.client');
        $response = $httpClient->get($apiBaseUrl . '/catalog/search/products', [
            // We forward the whole query string to the API ('query')
            'query' => $request->getQueryString(),
        ]);

        return new JsonResponse(json_decode($response->getBody()->getContents(), true));
    }
}
