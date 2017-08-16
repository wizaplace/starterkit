<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Catalog\DeclinationOption;
use Wizaplace\Catalog\Review\ReviewService;
use Wizaplace\Seo\SeoService;
use Wizaplace\Seo\SlugTargetType;

class ProductController extends Controller
{
    public function viewAction(SeoService $seoService, string $categoryPath, string $slug, Request $request) : Response
    {
        $slugTarget = $seoService->resolveSlug($slug);
        if (is_null($slugTarget) || $slugTarget->getObjectType() != SlugTargetType::PRODUCT()) {
            throw $this->createNotFoundException("Product '${slug}' Not Found");
        }
        $productId = (int) $slugTarget->getObjectId();

        $product = $this->get(CatalogService::class)->getProductById($productId);

        // Recovering the declinationId from url, if none passed, declination = first declination of the product
        if (!$declinationId = $request->query->get('d')) {
            $declinationId = $product->getDeclinations()[0]->getId();
        }

        $declination = $product->getDeclination($declinationId);

        $declinationVariantIds = array_map(function (DeclinationOption $option) {
            return $option->getVariantId();
        }, $declination->getOptions());

        $realCategoryPath = implode('/', $product->getCategorySlugs());
        if ($categoryPath !== $realCategoryPath) {
            return $this->redirect($this->generateUrl('product', ['categoryPath' => $realCategoryPath, 'slug' => $product->getSlug()]));
        }

        // latestProducts
        $catalogService = $this->get(CatalogService::class);
        $latestProducts = $catalogService->search('', [], ['createdAt' => 'desc'], 6)->getProducts();

        //product Reviews
        $reviewService = $this->get(ReviewService::class);
        $reviews = $reviewService->getProductReviews($productId);

        return $this->render('product/product.html.twig', [
            'product' => $product,
            'latestProducts' => $latestProducts,
            'reviews' => $reviews,
            'declination' => $declination,
            'declinationVariantIds' => $declinationVariantIds,
        ]);
    }

    public function getDeclinationIdAction(Request $request): JsonResponse
    {
        $productId = $request->query->get('productId');
        $variantIds = $request->query->get('variantIds');

        $catalogService = $this->get(CatalogService::class);

        $product = $catalogService->getProductById($productId);
        $declination = $product->getDeclinationFromOptions($variantIds);


        return new JsonResponse($declination->getId());
    }

    public function reviewAction(ReviewService $reviewService, Request $request) : RedirectResponse
    {
        $reviewService->reviewProduct(
            (int) $request->request->get('product_id'),
            (string) $request->request->get('author'),
            (string) $request->request->get('message'),
            (int) $request->request->get('rating')
        );

        return $this->redirect($request->request->get('redirect_url'));
    }
}
