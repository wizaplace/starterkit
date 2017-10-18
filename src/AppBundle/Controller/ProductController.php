<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\Review\ReviewService;
use WizaplaceFrontBundle\Controller\ProductController as BaseController;

class ProductController extends BaseController
{
    public function viewAction(string $categoryPath, string $slug, Request $request) : Response
    {
        return parent::viewAction($categoryPath, $slug, $request);
    }

    public function reviewAction(ReviewService $reviewService, Request $request) : RedirectResponse
    {
        $reviewService->reviewProduct(
            (string) $request->request->get('product_id'),
            (string) $request->request->get('author'),
            (string) $request->request->get('message'),
            (int) $request->request->get('rating')
        );

        return $this->redirect($request->request->get('redirect_url'));
    }
}
