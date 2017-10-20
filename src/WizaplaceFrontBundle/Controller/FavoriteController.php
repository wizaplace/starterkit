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
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Favorite\Exception\FavoriteAlreadyExist;
use Wizaplace\SDK\Favorite\FavoriteService;

class FavoriteController extends Controller
{
    public function addToFavoriteAction(FavoriteService $favoriteService, Request $request): JsonResponse
    {
        $declinationId = $request->request->get('declinationId');
        try {
            $favoriteService->addDeclinationToUserFavorites($declinationId);
        } catch (FavoriteAlreadyExist $e) {
            $this->get('logger')->warn("Declination added twice in favorites'", ['declinationId' => $declinationId]);

            return new JsonResponse([
                'error' => [
                    'message' => 'already in favorites',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($declinationId);
    }

    public function removeFromFavoriteAction(FavoriteService $favoriteService, Request $request): JsonResponse
    {
        $declinationId = $request->request->get('declinationId');
        $favoriteService->removeDeclinationToUserFavorites($declinationId);

        return new JsonResponse($declinationId);
    }
}
