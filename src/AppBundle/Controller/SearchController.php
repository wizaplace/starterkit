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

class SearchController extends Controller
{
    public function searchAction(Request $request): Response
    {
        $selectedCategoryId = $request->get("selected_category_id");

        return $this->render('search/search.html.twig', [
            'selectedCategoryId' => $selectedCategoryId,
        ]);
    }
}
