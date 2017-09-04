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
use Wizaplace\Catalog\Option;
use Wizaplace\Catalog\OptionVariant;
use Wizaplace\Catalog\Review\ReviewService;
use Wizaplace\Favorite\FavoriteService;
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

        $declination = $product->getDeclination($declinationId);
        $variantIdByOptionId = [];
        foreach ($declination->getOptions() as $option) {
            $variantIdByOptionId[$option->getId()] = $option->getVariantId();
        }

        $options = array_map(function (Option $option) use ($product, $variantIdByOptionId, $categoryPath, $slug) {
            return [
                'id' => $option->getId(),
                'name' => $option->getName(),
                'variants' => array_map(function (OptionVariant $variant) use ($product, $option, $variantIdByOptionId, $categoryPath, $slug) {
                    $isSelected = $variantIdByOptionId[$option->getId()] === $variant->getId();

                    $variantIdByOptionId[$option->getId()] = $variant->getId();
                    $declinationId = $product->getDeclinationFromOptions($variantIdByOptionId)->getId();

                    return [
                        'id' => $variant->getId(),
                        'name' => $variant->getName(),
                        'selected' => $isSelected,
                        'url' => $this->generateUrl('product', [
                            'categoryPath' => $categoryPath,
                            'slug' => $slug,
                            'd' => $declinationId,
                        ]),
                    ];
                }, $option->getVariants()),
            ];
        }, $product->getOptions());

        $isFavorite = false;
        if ($this->getUser() !== null) {
            $favoriteService = $this->get(FavoriteService::class);
            $isFavorite = $favoriteService->isInFavorites($declinationId);
        }

        return $this->render('@App/product/product.html.twig', [
            'product' => $product,
            'latestProducts' => $latestProducts,
            'reviews' => $reviews,
            'declination' => $declination,
            'options' => $options,
            'isFavorite' => $isFavorite,
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
