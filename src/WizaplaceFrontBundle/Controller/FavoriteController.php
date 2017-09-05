<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wizaplace\Favorite\FavoriteService;

class FavoriteController extends Controller
{
    public function addToFavoriteAction(FavoriteService $favoriteService, Request $request): JsonResponse
    {
        $declinationId = $request->request->get('declinationId');
        $favoriteService->addDeclinationToUserFavorites($declinationId);

        return new JsonResponse();
    }

    public function removeFromFavoriteAction(FavoriteService $favoriteService, Request $request): JsonResponse
    {
        $declinationId = $request->request->get('declinationId');
        $favoriteService->removeDeclinationToUserFavorites($declinationId);

        return new JsonResponse();
    }
}
