<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    public function searchAction(): Response
    {
        $apiBaseUrl = $this->getParameter("api.base_url");

        return $this->render('search/search.html.twig', [
            'apiBaseUrl' => $apiBaseUrl,
        ]);
    }
}
