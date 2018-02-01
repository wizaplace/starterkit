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
use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\DeclinationId;
use Wizaplace\SDK\Catalog\ProductCategory;
use Wizaplace\SDK\Catalog\Review\ReviewService;
use Wizaplace\SDK\Seo\SeoService;
use WizaplaceFrontBundle\Controller\ProductController as BaseController;
use WizaplaceFrontBundle\Service\DeclinationService;
use WizaplaceFrontBundle\Service\FavoriteService;
use WizaplaceFrontBundle\Service\ProductListService;
use WizaplaceFrontBundle\Service\ProductUrlGenerator;

class ProductController extends BaseController
{
    /**
     * @var DeclinationService
     */
    private $declinationService;

    public function __construct(
        ProductUrlGenerator $productUrlGenerator,
        SeoService $seoService,
        CatalogService $catalogService,
        ProductListService $productListService,
        ReviewService $reviewService,
        FavoriteService $favoriteService,
        DeclinationService $declinationService
    ) {
        parent::__construct($productUrlGenerator, $seoService, $catalogService, $productListService, $reviewService, $favoriteService);
        $this->declinationService = $declinationService;
    }

    public function viewAction(string $categoryPath, string $slug, Request $request) : Response
    {
        $product = $this->getProductFromSlug($slug);
        if (!$product) {
            throw $this->createNotFoundException("Product '${slug}' Not Found");
        }

        // Check if the category path is canonical
        $realCategoryPath = implode('/', array_map(function (ProductCategory $category) : string {
            return $category->getSlug();
        }, $product->getCategoryPath()));
        if ($categoryPath !== $realCategoryPath) {
            // If not, redirect to the canonical URL
            return $this->redirect($this->productUrlGenerator->generateUrlFromProduct($product));
        }

        // latestProducts
        $latestProducts = $this->productListService->getLatestProducts(6);

        //product Reviews
        $reviews = $this->reviewService->getProductReviews($product->getId());

        if ($request->query->has('options')) {
            $template = '@App/product/product.html.twig';
            $isFavorite = false;
            $declination = null;
            $isAvailable = false;
            $images = $product->getImages();

            $optionsSelects = $this->declinationService->listProductOptionSelectsFromSelectedVariantsIds($product, $request->query->get('options'));
        } else {
            $template = '@App/product/product.declination.html.twig';
            // Recovering the declinationId from url, if none passed, declination = first declination of the product
            if (!$request->query->has('d')) {
                $declinationId = $product->getDeclinations()[0]->getId();
            } else {
                $declinationId = new DeclinationId($request->query->get('d'));
            }

            $isFavorite = $this->favoriteService->isInFavorites($declinationId);
            $declination = $product->getDeclination($declinationId);
            $isAvailable = $declination->isAvailable();
            $images = $declination->getImages();

            $optionsSelects =  $this->declinationService->listProductOptionSelectsFromSelectedDeclination($product, $declination);
        }

        return $this->render($template, [
            'product' => $product,
            'latestProducts' => $latestProducts,
            'reviews' => $reviews,
            'declination' => $declination,
            'optionsSelects' => $optionsSelects,
            'isFavorite' => $isFavorite,
            'isAvailable' => $isAvailable,
            'images' => $images,
        ]);
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
