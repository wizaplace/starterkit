<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\Catalog\CatalogService;
use Wizaplace\Catalog\Review\ReviewService;
use Wizaplace\Seo\SeoService;
use Wizaplace\Seo\SlugTargetType;

class ProductController extends Controller
{
    public function viewAction(SeoService $seoService, string $categoryPath, string $slug) : Response
    {
        $slugTarget = $seoService->resolveSlug($slug);
        if (is_null($slugTarget) || $slugTarget->getObjectType() != SlugTargetType::PRODUCT()) {
            throw $this->createNotFoundException("Product '${slug}' Not Found");
        }
        $productId = (int) $slugTarget->getObjectId();

        $product = $this->get(CatalogService::class)->getProductById($productId);

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
        ]);
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
